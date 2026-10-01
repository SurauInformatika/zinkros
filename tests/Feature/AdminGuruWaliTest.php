<?php

namespace Tests\Feature;

use App\Models\ClassHomeroom;
use App\Models\ClassRoom;
use App\Models\User;
use Tests\TestCase;

class AdminGuruWaliTest extends TestCase
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

    public function test_admin_can_view_create_and_store_guru_with_wali(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/admin/guru/create')
            ->assertOk()
            ->assertSee('Jadikan sebagai Wali Kelas', false)
            ->assertSee('Wali 1', false)
            ->assertSee('Wali 2', false);

        $className = 'WaliFormTmp ' . now()->timestamp;
        $class = ClassRoom::create([
            'school_id' => $admin->school_id,
            'class_name' => $className,
            'grade_level' => '9',
        ]);

        $suffix = now()->timestamp;
        $email = 'wali.form.' . $suffix . '@mail.com';

        try {
            $this->post('/admin/guru', [
                'name' => 'Wali Form ' . $suffix,
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
                'is_wali' => '1',
                'wali_class_id' => $class->id,
                'wali_sort' => '2',
            ])->assertSessionHas('status');

            $guru = User::where('email', $email)->firstOrFail();
            $this->assertTrue($guru->is_wali_kelas);
            $this->assertDatabaseHas('class_homerooms', [
                'class_id' => $class->id,
                'user_id' => $guru->id,
                'sort' => 2,
            ]);

            $this->get('/admin/guru/' . $guru->id . '/edit')
                ->assertOk()
                ->assertSee('Jadikan sebagai Wali Kelas', false);
        } finally {
            $class->delete();
            User::where('email', $email)->delete();
        }
    }

    public function test_wali_slot_already_taken_rejected(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $owner = User::create([
            'school_id' => $admin->school_id,
            'name' => 'Owner Tmp ' . now()->timestamp,
            'role' => 'guru',
            'email' => 'owner.tmp.' . now()->timestamp . '@mail.com',
            'password' => 'password',
        ]);
        $suffix = now()->timestamp;
        $class = ClassRoom::create([
            'school_id' => $admin->school_id,
            'class_name' => 'WaliTaken ' . $suffix,
            'grade_level' => '7',
        ]);
        ClassHomeroom::create([
            'school_id' => $admin->school_id,
            'class_id' => $class->id,
            'user_id' => $owner->id,
            'sort' => 1,
        ]);

        $email = 'wali.taken.' . $suffix . '@mail.com';

        try {
            $this->post('/admin/guru', [
                'name' => 'Wali Taken ' . $suffix,
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
                'is_wali' => '1',
                'wali_class_id' => $class->id,
                'wali_sort' => '1',
            ])->assertSessionHasErrors('wali_sort');

            $this->assertSame(1, ClassHomeroom::where('class_id', $class->id)->count());
        } finally {
            $class->delete();
            $owner->delete();
            User::where('email', $email)->delete();
        }
    }

    public function test_admin_guru_index_displays_gender_and_filters(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $ts = now()->timestamp;
        $male = User::create([
            'school_id' => $admin->school_id,
            'name' => 'Guru L ' . $ts,
            'email' => 'guru.l.' . $ts . '@test.id',
            'password' => 'password',
            'role' => 'guru',
            'gender' => 'L',
        ]);
        $female = User::create([
            'school_id' => $admin->school_id,
            'name' => 'Guru P ' . $ts,
            'email' => 'guru.p.' . $ts . '@test.id',
            'password' => 'password',
            'role' => 'guru',
            'gender' => 'P',
        ]);

        try {
            $this->get(route('admin.guru.index'))
                ->assertOk()
                ->assertSee('Jenis Kelamin', false)
                ->assertSee('Guru L ' . $ts)
                ->assertSee('Guru P ' . $ts)
                ->assertSee('Laki-laki')
                ->assertSee('Perempuan');

            $this->get(route('admin.guru.index', ['gender' => 'L']))
                ->assertOk()
                ->assertSee('Guru L ' . $ts)
                ->assertDontSee('Guru P ' . $ts);

            $this->get(route('admin.guru.index', ['gender' => 'P']))
                ->assertOk()
                ->assertDontSee('Guru L ' . $ts)
                ->assertSee('Guru P ' . $ts);
        } finally {
            $male->delete();
            $female->delete();
        }
    }

    public function test_admin_pengguna_create_form_renders_gender_for_kepsek_and_wakasek(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('admin.pengguna.create', ['role' => 'kepsek']))
            ->assertOk()
            ->assertSee('Jenis Kelamin', false);

        $this->get(route('admin.pengguna.create', ['role' => 'wakasek']))
            ->assertOk()
            ->assertSee('Jenis Kelamin', false);
    }

    public function test_admin_pengguna_store_saves_gender_for_kepsek(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $suffix = now()->timestamp;
        $email = 'pengguna.gender.kepsek.' . $suffix . '@mail.com';

        try {
            $this->post(route('admin.pengguna.store'), [
                'name' => 'Kepsek Gender ' . $suffix,
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'kepsek',
                'gender' => 'L',
            ])->assertRedirect()->assertSessionHas('status');

            $user = User::where('email', $email)->firstOrFail();
            $this->assertSame('kepsek', $user->role);
            $this->assertSame('L', $user->gender);
        } finally {
            User::where('email', $email)->delete();
        }
    }

    public function test_admin_pengguna_store_saves_gender_for_wakasek(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $suffix = now()->timestamp;
        $email = 'pengguna.gender.wakasek.' . $suffix . '@mail.com';

        try {
            $this->post(route('admin.pengguna.store'), [
                'name' => 'Wakasek Gender ' . $suffix,
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'wakasek',
                'position' => 'humas',
                'gender' => 'P',
            ])->assertRedirect()->assertSessionHas('status');

            $user = User::where('email', $email)->firstOrFail();
            $this->assertSame('wakasek', $user->role);
            $this->assertSame('humas', $user->position);
            $this->assertSame('P', $user->gender);
        } finally {
            User::where('email', $email)->delete();
        }
    }
}
