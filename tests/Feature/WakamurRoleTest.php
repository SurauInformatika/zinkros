<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WakamurRoleTest extends TestCase
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

    private function actingAsRole(string $role): User
    {
        $email = [
            'admin' => 'admin@sit.sch.id',
            'wakasek' => 'wakakur@sit.sch.id',
            'kepsek' => 'kepsek@sit.sch.id',
        ][$role];

        $user = User::where('email', $email)->firstOrFail();
        $this->actingAs($user);

        return $user;
    }

    public function test_wakasek_pages_render(): void
    {
        $this->actingAsRole('wakasek');

        $this->get('/wakasek/dashboard')->assertOk()->assertSee('Dashboard Wakil Kepala Sekolah', false);
        $this->get('/wakasek/siswa')->assertOk()->assertSee('Daftar Siswa', false);
        $this->get('/wakasek/kelas')->assertOk();
    }

    public function test_wakasek_cannot_access_admin_pages(): void
    {
        $this->actingAsRole('wakasek');

        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_pengguna_tabs_include_leadership_roles(): void
    {
        $admin = $this->actingAsRole('admin');

        foreach (['kepsek', 'wakasek'] as $role) {
            $resp = $this->get('/admin/pengguna?role=' . $role);
            $resp->assertOk();
        }

        $resp = $this->get('/admin/pengguna?role=wakasek');
        $resp->assertSee('Wakil Kepala Sekolah', false);

        // Create a wakasek account via admin Pengguna
        $uniqueEmail = 'wakasek.test.' . now()->timestamp . '@mail.com';
        $resp = $this->post('/admin/pengguna', [
            'name' => 'Waka Kesiswaan Test',
            'email' => $uniqueEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'wakasek',
            'position' => 'kesiswaan',
        ]);

        $resp->assertRedirect();

        $created = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($created);
        $this->assertEquals('wakasek', $created->role);
        $this->assertEquals('kesiswaan', $created->position);
        $this->assertEquals($admin->id, $created->created_by);

        $created->delete();
    }

    public function test_created_by_recorded_for_guru() : void
    {
        $admin = $this->actingAsRole('admin');

        $uniqueEmail = 'guru.audit.' . now()->timestamp . '@mail.com';
        $resp = $this->post('/admin/guru', [
            'name' => 'Guru Audit',
            'email' => $uniqueEmail,
            'phone' => null,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $resp->assertRedirect();

        $guru = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($guru);
        $this->assertEquals('guru', $guru->role);
        $this->assertEquals($admin->id, $guru->created_by);

        $guru->delete();
    }
}