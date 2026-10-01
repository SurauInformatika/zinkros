<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SchoolRoleLabelsTest extends TestCase
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

    public function test_custom_role_labels_render_throughout(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->role_labels;
        $originalRoles = $school->pengguna_roles;

        try {
            $this->actingAs($admin);

            $this->put('/admin/setting/school', [
                'name' => $school->name,
                'primary_color' => $school->primary_color,
                'secondary_color' => $school->secondary_color,
                'role_labels' => [
                    'kepsek' => 'Mudir',
                    'wakasek' => 'Mudir Tarbiyah',
                ],
            ])->assertRedirect();

            $school->refresh();
            $this->assertEquals('Mudir', $school->roleLabel('kepsek'));
            $this->assertEquals('Mudir Tarbiyah', $school->roleLabel('wakasek'));

            // Non-leadership roles keep fixed terms and are untouched
            $this->assertEquals('Orang Tua', $school->roleLabel('ortu'));

            // Menu Pengguna tab uses the custom term
            $this->get('/admin/pengguna?role=wakasek')->assertSee('Mudir Tarbiyah', false);

            // Wakasek dashboard heading uses the custom term
            $wakasek = User::where('email', 'wakamur@sit.sch.id')->firstOrFail();
            $this->actingAs($wakasek);
            $this->get('/wakasek/dashboard')->assertSee('Dashboard Mudir Tarbiyah', false);
        } finally {
            $school->forceFill(['role_labels' => $original, 'pengguna_roles' => $originalRoles])->save();
        }
    }

    public function test_all_pengguna_roles_labels_customizable_and_hidden_role_tab_disappears(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $originalLabels = $school->role_labels;
        $originalRoles = $school->pengguna_roles;

        try {
            $this->actingAs($admin);

            $this->put('/admin/setting/school', [
                'name' => $school->name,
                'primary_color' => $school->primary_color,
                'secondary_color' => $school->secondary_color,
                'role_labels' => [
                    'ortu' => 'Wali Murid',
                    'staff' => 'Staf Asrama',
                    'keuangan' => 'Bendahara',
                ],
                'pengguna_roles' => ['kepsek', 'wakasek', 'guru', 'ortu'],
            ])->assertRedirect();

            $school->refresh();

            $this->assertEquals('Wali Murid', $school->roleLabel('ortu'));
            $this->assertEquals('Staf Asrama', $school->roleLabel('staff'));
            $this->assertEquals('Bendahara', $school->roleLabel('keuangan'));

            $this->assertNotContains('staff', $school->penggunaRoles());

            // Disabled role tab disappears; enabled tab uses custom term
            $resp = $this->get('/admin/pengguna');
            $resp->assertSee('Wali Murid', false);
            $resp->assertDontSee('role=staff', false);

            // Requesting the disabled role redirects to its dedicated page
            $this->get('/admin/pengguna?role=staff')->assertRedirect();
        } finally {
            $school->forceFill(['role_labels' => $originalLabels, 'pengguna_roles' => $originalRoles])->save();
        }
    }

    public function test_default_role_labels_used_when_not_customized(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->role_labels;

        try {
            $school->forceFill(['role_labels' => null])->save();

            $this->assertEquals('Kepala Sekolah', $school->roleLabel('kepsek'));
            $this->assertEquals('Wakil Kepala Sekolah', $school->roleLabel('wakasek'));
        } finally {
            $school->forceFill(['role_labels' => $original])->save();
        }
    }
}