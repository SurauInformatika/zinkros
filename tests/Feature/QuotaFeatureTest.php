<?php

namespace Tests\Feature;

use App\Exceptions\QuotaExceededException;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotaFeatureTest extends TestCase
{
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

    public function test_siswa_create_blocked_when_quota_full(): void
    {
        $admin = User::where('email', 'adhir@mail.com')->firstOrFail();
        $school = $admin->school;
        $original = $school->plan;

        $this->actingAs($admin);

        config(['plans.plans.'.$school->plan.'.quota.siswa' => $school->quotaUsed('siswa') + 1]);

        $classId = $school->classes()->firstOrFail()->id;

        $payload = fn ($nis) => [
            'name' => 'Siswa Kuota '.$nis,
            'gender' => 'L',
            'nis' => $nis,
            'class_id' => $classId,
        ];

        try {
            $okNis = 'QY-'.Str::random(8);
            $this->post(route('admin.siswa.store'), $payload($okNis))
                ->assertSessionHas('status');
            $this->assertDatabaseHas('students', ['nis' => $okNis]);

            $blockedNis = 'QY-'.Str::random(8);
            $this->post(route('admin.siswa.store'), $payload($blockedNis))
                ->assertRedirect()
                ->assertSessionMissing('status');
            $this->assertStringContainsString('Kuota Siswa', session('error'));
            $this->assertDatabaseMissing('students', ['nis' => $blockedNis]);
        } finally {
            Student::where('nis', 'like', 'QY-%')->delete();
            $school->update(['plan' => $original]);
        }
    }

    public function test_guru_create_blocked_when_quota_full(): void
    {
        $admin = User::where('email', 'adhir@mail.com')->firstOrFail();
        $school = $admin->school;
        $original = $school->plan;

        $this->actingAs($admin);

        config(['plans.plans.'.$school->plan.'.quota.guru' => $school->quotaUsed('guru') + 1]);

        $payload = fn ($email) => [
            'name' => 'Guru Kuota',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $created = [];

        try {
            $okEmail = 'quota-guru-'.Str::random(8).'@example.test';
            $this->post(route('admin.guru.store'), $payload($okEmail))
                ->assertSessionHas('status');
            $created[] = $okEmail;
            $this->assertDatabaseHas('users', ['email' => $okEmail]);

            $blockedEmail = 'quota-guru-'.Str::random(8).'@example.test';
            $this->post(route('admin.guru.store'), $payload($blockedEmail))
                ->assertRedirect()
                ->assertSessionMissing('status');
            $this->assertStringContainsString('Kuota Guru', session('error'));
            $this->assertDatabaseMissing('users', ['email' => $blockedEmail]);
        } finally {
            User::whereIn('email', $created)->delete();
            $school->update(['plan' => $original]);
        }
    }

    public function test_quota_enforcement_is_skipped_for_ortu_and_superadmin(): void
    {
        $admin = User::where('email', 'adhir@mail.com')->firstOrFail();
        $school = $admin->school;
        $original = $school->plan;

        $this->actingAs($admin);

        config(['plans.plans.'.$school->plan.'.quota.guru' => $school->quotaUsed('guru')]);

        try {
            $email = 'quota-ortu-'.Str::random(8).'@example.test';

            $this->post(route('admin.siswa.ortu.store'), [
                'name' => 'Ortu Kuota',
                'email' => $email,
            ])->assertOk();

            $this->assertDatabaseHas('users', ['email' => $email]);
        } finally {
            User::where('email', 'like', 'quota-ortu-%')->delete();
            $school->update(['plan' => $original]);
        }
    }

    public function test_unlimited_plan_accepts_creation_beyond_numeric_quota(): void
    {
        $admin = User::where('email', 'adhir@mail.com')->firstOrFail();
        $school = $admin->school;
        $original = $school->plan;

        $school->update(['plan' => School::PLAN_PRO_MAX]);

        config(['plans.plans.'.School::PLAN_PRO.'.quota.siswa' => 0]);
        config(['plans.plans.'.School::PLAN_PRO.'.quota.guru' => 0]);

        $classId = $school->classes()->firstOrFail()->id;

        try {
            $student = Student::create([
                'school_id' => $school->id,
                'name' => 'Siswa Pro-Max Kuota',
                'gender' => 'P',
                'nis' => 'QX-'.Str::random(8),
                'class_id' => $classId,
            ]);

            $this->assertNotNull($student->id);
            $this->assertNull($school->quotaRemaining('siswa'));
        } finally {
            $student?->delete();
            $school->update(['plan' => $original]);
        }
    }

    public function test_quota_guard_throws_when_called_directly(): void
    {
        $school = School::where('name', 'SDIT Insan Madani Bone')->firstOrFail();
        $original = $school->plan;

        try {
            config(['plans.plans.'.$school->plan.'.quota.siswa' => $school->quotaUsed('siswa')]);

            $this->expectException(QuotaExceededException::class);
            $school->assertWithinQuota('siswa');
        } finally {
            $school->update(['plan' => $original]);
        }
    }
}