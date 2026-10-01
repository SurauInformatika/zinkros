<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class EducationLevelTest extends TestCase
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

    public function test_school_registration_saves_education_level(): void
    {
        $unique = Str::uuid()->toString();
        $email = 'reg-'.$unique.'@example.test';
        $schoolName = 'Test School '.$unique;

        $this->post(route('auth.register.post'), [
            'school_name' => $schoolName,
            'education_level' => 'sd',
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
            $this->assertSame('sd', $school->education_level);
            $this->assertNotNull($user);
            $this->assertSame($school->id, $user->school_id);
            $this->assertSame('admin', $user->role);
            $this->assertAuthenticatedAs($user);
        } finally {
            $user?->delete();
            $school?->delete();
        }
    }

    public function test_register_rejects_invalid_education_level(): void
    {
        $unique = Str::uuid()->toString();

        $this->post(route('auth.register.post'), [
            'school_name' => 'Test School '.$unique,
            'education_level' => 'xyz',
            'name' => 'Test Admin',
            'email' => 'reg-invalid-'.$unique.'@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('education_level');
    }

    public function test_kelas_form_is_filtered_by_school_education_level(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->education_level;

        try {
            $school->update(['education_level' => 'sd']);
            $this->actingAs($admin);

            $this->get(route('admin.kelas.create'))
                ->assertOk()
                ->assertSee('Kelas 1', false)
                ->assertSee('Kelas 6', false)
                ->assertDontSee('Kelas 7')
                ->assertDontSee('Kelas 10')
                ->assertDontSee('TK B')
                ->assertDontSee('Kelompok A');
        } finally {
            $school->update(['education_level' => $original]);
        }
    }

    public function test_kelas_form_shows_all_levels_without_school_level(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->education_level;

        try {
            $school->update(['education_level' => null]);
            $this->actingAs($admin);

            $this->get(route('admin.kelas.create'))
                ->assertOk()
                ->assertSee('Kelas 1', false)
                ->assertSee('Kelas 7', false)
                ->assertSee('Kelas 10', false)
                ->assertSee('TK B', false);
        } finally {
            $school->update(['education_level' => $original]);
        }
    }

    public function test_kelas_store_rejects_grade_level_outside_school_level(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->education_level;
        $className = 'ZZ-'.Str::uuid()->toString();
        $created = null;

        try {
            $school->update(['education_level' => 'sd']);
            $this->actingAs($admin);

            $this->post(route('admin.kelas.store'), [
                'class_name' => $className,
                'grade_level' => '9',
            ])->assertSessionHasErrors('grade_level');

            $this->post(route('admin.kelas.store'), [
                'class_name' => $className,
                'grade_level' => '3',
            ])->assertRedirect(route('admin.kelas.index'))->assertSessionHas('status');

            $created = ClassRoom::where('class_name', $className)->first();
            $this->assertNotNull($created);
            $this->assertSame('3', $created->grade_level);
        } finally {
            $created?->delete();
            $school->update(['education_level' => $original]);
        }
    }

    public function test_school_education_level_set_until_locked(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->education_level;

        try {
            $school->update(['education_level' => null]);
            $this->actingAs($admin);

            $this->put(route('admin.setting.school.update'), [
                'name' => $school->name,
                'primary_color' => $school->primary_color,
                'secondary_color' => $school->secondary_color,
                'education_level' => 'smp',
            ])->assertRedirect(route('admin.setting.school'))->assertSessionHas('status');

            $this->assertSame('smp', $school->fresh()->education_level);
        } finally {
            $school->update(['education_level' => $original]);
        }
    }

    public function test_school_education_level_is_locked_once_set(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $school = $admin->school;
        $original = $school->education_level;

        try {
            $school->update(['education_level' => 'tk']);
            $this->actingAs($admin);

            $this->get(route('admin.setting.school'))
                ->assertOk()
                ->assertSee('cursor-not-allowed', false);

            $this->put(route('admin.setting.school.update'), [
                'name' => $school->name,
                'primary_color' => $school->primary_color,
                'secondary_color' => $school->secondary_color,
                'education_level' => 'sma',
            ])->assertRedirect(route('admin.setting.school'));

            $this->assertSame('tk', $school->fresh()->education_level);
        } finally {
            $school->update(['education_level' => $original]);
        }
    }
}