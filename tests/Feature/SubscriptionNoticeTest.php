<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Tests\TestCase;

class SubscriptionNoticeTest extends TestCase
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

    public function test_active_billing_soon_shows_warning_on_dashboard_and_billing(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $originalBilling = $school->next_billing_at;

        $this->actingAs($admin);

        $school->update(['next_billing_at' => now()->addDays(5)]);

        try {
            $this->get(route('admin.dashboard'))
                ->assertOk()
                ->assertSee('Tagihan akan segera jatuh tempo');

            $this->get(route('admin.setting.billing'))
                ->assertOk()
                ->assertSee('Tagihan akan segera jatuh tempo');
        } finally {
            $school->update(['next_billing_at' => $originalBilling]);
        }
    }

    public function test_overdue_billing_shows_danger_with_grace_left(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $originalBilling = $school->next_billing_at;

        $this->actingAs($admin);

        $school->update(['next_billing_at' => now()->subDay()]);

        try {
            $this->get(route('admin.dashboard'))
                ->assertOk()
                ->assertSee('Pembayaran belum diterima')
                ->assertSee('Masa tenggang tersisa 6 hari');
        } finally {
            $school->update(['next_billing_at' => $originalBilling]);
        }
    }

    public function test_far_billing_hides_notice(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $originalBilling = $school->next_billing_at;

        $this->actingAs($admin);

        $school->update(['next_billing_at' => now()->addDays(30)]);

        try {
            $this->get(route('admin.dashboard'))
                ->assertOk()
                ->assertDontSee('jatuh tempo')
                ->assertDontSee('Pembayaran belum diterima');
        } finally {
            $school->update(['next_billing_at' => $originalBilling]);
        }
    }

    public function test_expiring_trial_shows_warning(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $originalStatus = $school->status;
        $originalTrialEnds = $school->trial_ends_at;

        $this->actingAs($admin);

        $school->update([
            'status' => School::STATUS_TRIAL,
            'trial_ends_at' => now()->addDays(5),
        ]);

        try {
            $this->get(route('admin.dashboard'))
                ->assertOk()
                ->assertSee('Trial hampir berakhir');
        } finally {
            $school->update([
                'status' => $originalStatus,
                'trial_ends_at' => $originalTrialEnds,
            ]);
        }
    }

    public function test_no_notice_for_full_active_school(): void
    {
        $school = School::where('name', 'SDIT Insan Madani Bone')->firstOrFail();
        $original = [
            'status' => $school->status,
            'plan' => $school->plan,
            'next_billing_at' => $school->next_billing_at,
        ];

        $school->update([
            'status' => School::STATUS_ACTIVE,
            'plan' => School::PLAN_PRO,
            'next_billing_at' => now()->addMonths(3),
        ]);

        try {
            $this->assertNull($school->billingNotice());
        } finally {
            $school->update($original);
        }
    }
}