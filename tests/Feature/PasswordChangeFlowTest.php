<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeFlowTest extends TestCase
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

    private function makeNewOrtu(): User
    {
        return User::create([
            'school_id' => '01a01792-3680-7215-ba72-9b545e824c1d',
            'name' => 'Ortu Baru Test ' . uniqid(),
            'email' => 'ortubaru_' . uniqid() . '@mail.com',
            'role' => 'ortu',
            'position' => 'Orang Tua',
            'password' => Hash::make(User::DEFAULT_FIRST_PASSWORD),
        ]);
    }

    public function test_orthu_with_default_password_is_forced_to_change(): void
    {
        $ortu = $this->makeNewOrtu();
        $this->actingAs($ortu);

        $resp = $this->get(route('ortu.dashboard'));
        $resp->assertRedirect(route('ortu.password.change'));

        $ortu->delete();
    }

    public function test_orthu_can_change_password_and_reach_dashboard(): void
    {
        $ortu = $this->makeNewOrtu();
        $this->actingAs($ortu);

        $resp = $this->post(route('ortu.password.change.store'), [
            'current_password' => User::DEFAULT_FIRST_PASSWORD,
            'password' => 'rahasiaBaru123',
            'password_confirmation' => 'rahasiaBaru123',
        ]);

        $resp->assertRedirect(route('ortu.dashboard'));
        $ortu->refresh();
        $this->assertNotNull($ortu->password_changed_at);
        $this->assertFalse($ortu->mustChangePassword());
        $this->assertTrue(Hash::check('rahasiaBaru123', $ortu->password));

        // no longer forced
        $resp2 = $this->get(route('ortu.dashboard'));
        $resp2->assertOk();

        $ortu->delete();
    }

    public function test_login_post_redirects_new_orthu_to_change_page(): void
    {
        $ortu = $this->makeNewOrtu();

        $resp = $this->post(route('auth.login.post'), [
            'email' => $ortu->email,
            'password' => User::DEFAULT_FIRST_PASSWORD,
        ]);

        $resp->assertRedirect(route('ortu.password.change'));

        $ortu->delete();
    }
}
