<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolPlanLog;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlanFeatureTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'sit_school']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);
    }

    public function test_plan_helpers_are_cumulative(): void
    {
        $basic = new School(['plan' => School::PLAN_BASIC]);

        $this->assertTrue($basic->planHas('absensi'));
        $this->assertTrue($basic->planHas('nilai'));
        $this->assertTrue($basic->planHas('hafalan'));
        $this->assertTrue($basic->planHas('kaldik'));
        $this->assertFalse($basic->planHas('rfid'));
        $this->assertFalse($basic->planHas('multi_unit'));
        $this->assertSame(250, $basic->quota('siswa'));
        $this->assertSame(30, $basic->quota('guru'));
        $this->assertSame('Basic', $basic->planLabel());

        $free = new School(['plan' => School::PLAN_FREE]);
        $this->assertFalse($free->planHas('nilai'));
        $this->assertTrue($free->planHas('absensi'));

        $proMax = new School(['plan' => School::PLAN_PRO_MAX]);
        $this->assertTrue($proMax->planHas('rfid'));
        $this->assertTrue($proMax->planHas('multi_unit'));
        $this->assertTrue($proMax->planHas('website_api'));
        $this->assertNull($proMax->quota('siswa'));
    }

    public function test_registration_starts_pro_max_during_trial(): void
    {
        $unique = Str::uuid()->toString();
        $email = 'plan-reg-'.$unique.'@example.test';
        $schoolName = 'Test School '.$unique;

        $this->post(route('auth.register.post'), [
            'school_name' => $schoolName,
            'education_level' => 'smp',
            'name' => 'Test Admin',
            'email' => $email,
            'phone' => '08'.str_pad((string) random_int(0, 99999999), 8, '0'),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $school = School::where('name', $schoolName)->first();
        $user = User::where('email', $email)->first();

        try {
            $this->assertNotNull($school);
            $this->assertSame(School::PLAN_PRO_MAX, $school->plan);
            $this->assertSame(School::STATUS_TRIAL, $school->status);
            $this->assertNotNull($school->trial_ends_at);
            $this->assertTrue($school->trial_ends_at->gt(Carbon::now()->addDays(13)));
        } finally {
            $user?->delete();
            $school?->delete();
        }
    }

    public function test_paid_features_accessible_on_pro_plan(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->plan;

        try {
            $school->update(['plan' => School::PLAN_PRO]);
            $this->actingAs($admin);

            $this->get(route('admin.grade-types.index'))->assertOk();
            $this->get(route('admin.quran.index'))->assertOk();
            $this->get(route('admin.academic-years.index'))->assertOk();

            $this->get(route('admin.dashboard'))
                ->assertSee('Master Quran')
                ->assertSee('Tipe Nilai')
                ->assertSee('Tahun Ajaran');
        } finally {
            $school->update(['plan' => $original]);
        }
    }

    public function test_paid_features_blocked_on_free_plan(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->plan;

        try {
            $school->update(['plan' => School::PLAN_FREE]);
            $this->actingAs($admin);

            $this->get(route('admin.grade-types.index'))->assertForbidden();
            $this->get(route('admin.quran.index'))->assertForbidden();
            $this->get(route('admin.academic-years.index'))->assertForbidden();

            $this->get(route('admin.dashboard'))
                ->assertOk()
                ->assertDontSee('Master Quran')
                ->assertDontSee('Tipe Nilai')
                ->assertDontSee('Tahun Ajaran')
                ->assertSee('Kelas');
        } finally {
            $school->update(['plan' => $original]);
        }
    }

    public function test_billing_page_shows_plan_and_features(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->plan;

        try {
            $school->update(['plan' => School::PLAN_PRO]);
            $this->actingAs($admin);

            $this->get(route('admin.setting.billing'))
                ->assertOk()
                ->assertSee('Paket Pro')
                ->assertSee('Fitur Paket')
                ->assertSee('Input Nilai & e-Rapor')
                ->assertSee('Gate Attendance RFID')
                ->assertSee('Support Prioritas');
        } finally {
            $school->update(['plan' => $original]);
        }
    }

    public function test_superadmin_can_change_school_plan_with_log(): void
    {
        $superadmin = User::where('email', 'superadmin@platform.id')->firstOrFail();
        $school = School::firstOrFail();
        $original = $school->plan;
        $target = $original === School::PLAN_PRO_MAX ? School::PLAN_BASIC : School::PLAN_PRO_MAX;

        try {
            $this->actingAs($superadmin);

            $this->post(route('platform.schools.plan', $school), [
                'plan' => $target,
                'note' => 'Test ganti paket',
            ])->assertRedirect();

            $school->refresh();
            $this->assertSame($target, $school->plan);

            $log = SchoolPlanLog::where('school_id', $school->id)
                ->where('from_plan', $original)
                ->where('to_plan', $target)
                ->latest()
                ->first();

            $this->assertNotNull($log);
            $this->assertSame('Test ganti paket', $log->note);
            $this->assertSame($superadmin->id, $log->created_by);

            $this->get(route('platform.schools.show', $school))
                ->assertOk()
                ->assertSee('Ubah Paket')
                ->assertSee('Riwayat Perubahan Paket')
                ->assertSee('Test ganti paket');
        } finally {
            $school->update(['plan' => $original]);
        }
    }

    public function test_activate_can_set_plan(): void
    {
        $superadmin = User::where('email', 'superadmin@platform.id')->firstOrFail();
        $school = School::firstOrFail();
        $original = $school->plan;
        $originalStatus = $school->status;

        try {
            $this->actingAs($superadmin);

            $this->post(route('platform.schools.activate', $school), [
                'amount' => 900000,
                'period_start' => Carbon::today()->subMonth()->toDateString(),
                'period_end' => Carbon::today()->addMonths(11)->toDateString(),
                'plan' => School::PLAN_PRO_MAX,
            ])->assertRedirect();

            $school->refresh();
            $this->assertSame(School::PLAN_PRO_MAX, $school->plan);
            $this->assertSame(School::STATUS_ACTIVE, $school->status);
            $this->assertTrue(Payment::where('school_id', $school->id)->where('amount', 900000)->exists());
            $this->assertTrue(SchoolPlanLog::where('school_id', $school->id)->where('to_plan', School::PLAN_PRO_MAX)->exists());
        } finally {
            Payment::where('school_id', $school->id)->where('amount', 900000)->delete();
            SchoolPlanLog::where('school_id', $school->id)->where('note', 'Test ganti paket')->delete();
            $school->update(['plan' => $original, 'status' => $originalStatus]);
        }
    }
}