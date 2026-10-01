<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('auth.login'));
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('auth.login'))->assertStatus(200);
    }

    public function test_admin_can_login_and_reach_dashboard(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->post(route('auth.login.post'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200);
    }

    public function test_guru_cannot_access_admin_dashboard(): void
    {
        $guru = User::factory()->create(['role' => User::ROLE_GURU]);

        $this->actingAs($guru)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('auth.login'));
    }
}
