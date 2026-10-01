<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlatformDashboardTest extends TestCase
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

    public function test_dashboard_shows_plan_breakdown_and_potential(): void
    {
        $this->actingAs(User::where('email', 'superadmin@platform.id')->firstOrFail());

        $this->get(route('platform.dashboard'))
            ->assertOk()
            ->assertSee('Distribusi Paket')
            ->assertSee('Potensi')
            ->assertSee('Perlu Perhatian')
            ->assertSee('Gratis')
            ->assertSee('Basic')
            ->assertSee('Pro Max');
    }

    public function test_dashboard_lists_schools_needing_attention(): void
    {
        $this->actingAs(User::where('email', 'superadmin@platform.id')->firstOrFail());

        $graceName = 'Test Grace '.Str::random(6);
        $expiredName = 'Test Expired '.Str::random(6);

        $grace = School::create([
            'name' => $graceName,
            'slug' => 'test-grace-'.Str::random(6),
            'plan' => School::PLAN_PRO,
            'status' => School::STATUS_ACTIVE,
            'next_billing_at' => Carbon::now()->subDay(),
        ]);

        $expired = School::create([
            'name' => $expiredName,
            'slug' => 'test-expired-'.Str::random(6),
            'plan' => School::PLAN_BASIC,
            'status' => School::STATUS_EXPIRED,
        ]);

        try {
            $this->get(route('platform.dashboard'))
                ->assertOk()
                ->assertSee($graceName)
                ->assertSee('Melewati tagihan, masa tenggang tersisa')
                ->assertSee($expiredName)
                ->assertSee('Status berakhir');
        } finally {
            $grace?->delete();
            $expired?->delete();
        }
    }
}