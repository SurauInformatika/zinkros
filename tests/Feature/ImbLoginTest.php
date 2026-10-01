<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImbLoginTest extends TestCase
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

        PlatformSetting::forget();
    }

    private function seedImb(): School
    {
        $school = School::updateOrCreate(
            ['slug' => 'imb-test'],
            ['name' => 'Lembaga IMBA Uji', 'status' => School::STATUS_ACTIVE, 'primary_color' => '#4338ca', 'secondary_color' => '#0ea5e9']
        );

        User::updateOrCreate(
            ['email' => 'imb@mail.com'],
            ['name' => 'Admin IMBA', 'school_id' => $school->id, 'role' => 'admin', 'password' => Hash::make('password')]
        );

        return $school;
    }

    public function test_imb_login_page_shows_school_branding_without_tagline(): void
    {
        $school = $this->seedImb();
        $school->update(['logo' => 'logos/imb-uji.png']);

        $tagline = PlatformSetting::tagline();

        $response = $this->get(route('auth.imb'));

        $response->assertOk();
        $response->assertSee('Lembaga IMBA Uji');
        $response->assertSee('storage/logos/imb-uji.png');

        if ($tagline) {
            $response->assertDontSee($tagline);
        }
    }

    public function test_general_login_keeps_platform_branding(): void
    {
        $this->seedImb();

        $response = $this->get(route('auth.login'));

        $response->assertOk();
        $response->assertSee(PlatformSetting::appName());
        $response->assertDontSee('Lembaga IMBA Uji');
    }

    public function test_imb_login_authenticates_imb_school_user(): void
    {
        $this->seedImb();

        $response = $this->post(route('auth.imb.post'), [
            'email' => 'imb@mail.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'imb@mail.com')->first());
    }

    public function test_imb_login_rejects_user_from_another_school(): void
    {
        $this->seedImb();

        $other = School::updateOrCreate(
            ['slug' => 'imb-other-test'],
            ['name' => 'Sekolah Lain', 'status' => School::STATUS_ACTIVE]
        );

        User::updateOrCreate(
            ['email' => 'other@test.local'],
            ['name' => 'User Lain', 'school_id' => $other->id, 'role' => 'admin', 'password' => Hash::make('password')]
        );

        $response = $this->post(route('auth.imb.post'), [
            'email' => 'other@test.local',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_imb_login_rejects_bad_credentials(): void
    {
        $this->seedImb();

        $response = $this->post(route('auth.imb.post'), [
            'email' => 'imb@mail.com',
            'password' => 'salah-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_of_imb_school_user_returns_to_branded_login(): void
    {
        $this->seedImb();

        $this->post(route('auth.imb.post'), [
            'email' => 'imb@mail.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $response = $this->post(route('auth.logout'));

        $response->assertRedirect(route('auth.imb'));
        $this->assertGuest();
    }

    public function test_logout_of_platform_user_returns_to_general_login(): void
    {
        $this->seedImb();

        $other = School::updateOrCreate(
            ['slug' => 'imb-other-test'],
            ['name' => 'Sekolah Lain', 'status' => School::STATUS_ACTIVE]
        );

        $user = User::updateOrCreate(
            ['email' => 'wakakur@sit.sch.id'],
            ['name' => 'Wakakur Uji', 'school_id' => $other->id, 'role' => 'wakakur', 'password' => Hash::make('password')]
        );

        $this->actingAs($user);

        $response = $this->post(route('auth.logout'));

        $response->assertRedirect(route('auth.login'));
        $this->assertGuest();
    }
}