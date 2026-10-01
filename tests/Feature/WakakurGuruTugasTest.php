<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class WakakurGuruTugasTest extends TestCase
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

    public function test_guru_page_renders_segments_and_filters(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $this->actingAs($wakakur);

        $resp = $this->get('/wakasek/guru');
        $resp->assertOk()
            ->assertSee('Guru', false)
            ->assertSee('Per Kelas', false)
            ->assertSee('Jenis Kelamin', false)
            ->assertSee('Guru Al-Quran', false);
    }

    public function test_per_kelas_segment_renders(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $this->actingAs($wakakur);

        $this->get('/wakasek/guru?view=kelas')
            ->assertOk()
            ->assertSee('Mapel Diampu', false);
    }

    public function test_old_plotting_route_redirects_to_per_kelas_segment(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $this->actingAs($wakakur);

        $this->get('/wakasek/pembagian-tugas')
            ->assertRedirect(route('wakasek.base.teachers', ['view' => 'kelas']));
    }

    public function test_gender_filter_filters_gurus(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $originalGender = $guru->gender;
        $guru->update(['gender' => 'P']);

        try {
            $this->actingAs($wakakur);

            $this->get('/wakasek/guru?gender=P')->assertSee($guru->name, false);
            $this->get('/wakasek/guru?gender=L')->assertDontSee($guru->name, false);
        } finally {
            $guru->update(['gender' => $originalGender]);
        }
    }

    public function test_gender_saved_via_admin_form_is_used_by_filter(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $suffix = now()->timestamp;
        $this->post('/admin/guru', [
            'name' => 'Guru Filter ' . $suffix,
            'gender' => 'L',
            'email' => 'filter.guru.' . $suffix . '@mail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $guru = User::where('email', 'filter.guru.' . $suffix . '@mail.com')->first();

        try {
            $this->assertNotNull($guru);
            $this->assertEquals('L', $guru->gender);

            $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
            $this->actingAs($wakakur);

            $this->get('/wakasek/guru?gender=L')->assertSee($guru->name, false);
            $this->get('/wakasek/guru?gender=P')->assertDontSee($guru->name, false);
        } finally {
            if ($guru) {
                $guru->delete();
            }
        }
    }

    public function test_guru_detail_page_renders(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $this->actingAs($wakakur);

        $this->get('/wakasek/guru/' . $guru->id)
            ->assertOk()
            ->assertSee($guru->name, false)
            ->assertSee($guru->email, false)
            ->assertSee('Plotting Mengajar', false)
            ->assertSee('Wali Kelas', false);
    }

    public function test_guru_detail_is_scoped_to_own_school(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $this->actingAs($wakakur);

        $suffix = now()->timestamp;
        $foreignSchool = \App\Models\School::create([
            'name' => 'Sekolah Lain Detail ' . $suffix,
            'slug' => 'sekolah-lain-detail-' . $suffix,
        ]);
        $foreignGuru = User::create([
            'school_id' => $foreignSchool->id,
            'name' => 'Guru Asing ' . $suffix,
            'role' => 'guru',
            'email' => 'foreign.detail.' . $suffix . '@mail.com',
            'password' => 'password',
        ]);

        try {
            $this->get('/wakasek/guru/' . $foreignGuru->id)->assertForbidden();
        } finally {
            $foreignGuru->delete();
            $foreignSchool->delete();
        }
    }

    public function test_guru_detail_rejects_non_guru(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($wakakur);

        $this->get('/wakasek/guru/' . $admin->id)->assertNotFound();
    }

    public function test_guru_detail_shows_wali_kelas(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $className = 'WaliTest ' . now()->timestamp;

        $class = \App\Models\ClassRoom::create([
            'school_id' => $wakakur->school_id,
            'class_name' => $className,
            'grade_level' => '7',
        ]);
        \App\Models\ClassHomeroom::create([
            'school_id' => $wakakur->school_id,
            'class_id' => $class->id,
            'user_id' => $guru->id,
            'sort' => 1,
        ]);

        try {
            $this->actingAs($wakakur);

            $this->get('/wakasek/guru/' . $guru->id)
                ->assertSee('Wali Kelas (', false)
                ->assertSee($className, false);
        } finally {
            $class->homerooms()->delete();
            $class->delete();
        }
    }

    public function test_guru_list_shows_specific_task_labels_and_no_kelas_diampu(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();

        $suffix = now()->timestamp;
        $class = \App\Models\ClassRoom::create([
            'school_id' => $wakakur->school_id,
            'class_name' => 'WaliList ' . $suffix,
            'grade_level' => '8',
        ]);
        \App\Models\ClassHomeroom::create([
            'school_id' => $wakakur->school_id,
            'class_id' => $class->id,
            'user_id' => $guru->id,
            'sort' => 1,
        ]);
        $subject = \App\Models\Subject::create([
            'school_id' => $wakakur->school_id,
            'name' => 'MapelList ' . $suffix,
            'type' => \App\Models\Subject::TYPE_GENERAL,
        ]);
        $plotting = \App\Models\ClassSubjectTeacher::create([
            'school_id' => $wakakur->school_id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $guru->id,
        ]);

        try {
            $this->actingAs($wakakur);

            $this->get('/wakasek/guru')
                ->assertDontSee('Kelas Diampu', false)
                ->assertSee('Wali Kelas ' . $class->class_name, false)
                ->assertSee('Guru ' . $subject->name, false);
        } finally {
            $plotting->delete();
            $subject->delete();
            $class->delete();
        }
    }

    public function test_teachers_are_scoped_to_own_school(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $this->actingAs($wakakur);

        $suffix = now()->timestamp;
        $foreignSchool = \App\Models\School::create([
            'name' => 'Sekolah Lain ' . $suffix,
            'slug' => 'sekolah-lain-' . $suffix,
        ]);

        $uniqueName = 'Guru Sekolah Lain ' . $suffix;
        $foreign = User::create([
            'school_id' => $foreignSchool->id,
            'name' => $uniqueName,
            'role' => 'guru',
            'email' => 'foreign.' . $suffix . '@mail.com',
            'password' => 'password',
        ]);

        try {
            $this->get('/wakasek/guru')->assertDontSee($uniqueName, false);
        } finally {
            $foreign->delete();
            $foreignSchool->delete();
        }
    }

    public function test_kepsek_can_manage_assignments(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $kepsek->school_id)->firstOrFail();
        $className = 'KepsekTest ' . now()->timestamp;
        $this->actingAs($kepsek);

        $class = \App\Models\ClassRoom::create([
            'school_id' => $kepsek->school_id,
            'class_name' => $className,
            'grade_level' => '9',
        ]);

        try {
            $this->get('/wakasek/guru/' . $guru->id)->assertOk()->assertSee('Plotting Mengajar', false);

            $this->put('/wakasek/guru/' . $guru->id . '/wali', ['class_id' => $class->id])
                ->assertRedirect()
                ->assertSessionHas('status');

            $this->assertDatabaseHas('class_homerooms', [
                'class_id' => $class->id,
                'user_id' => $guru->id,
            ]);
        } finally {
            $class->delete();
        }
    }

    public function test_wali_kelas_cannot_be_stolen_from_another_guru(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $this->actingAs($wakakur);

        $suffix = now()->timestamp;
        $owner = User::create([
            'school_id' => $wakakur->school_id,
            'name' => 'Pemilik Wali ' . $suffix,
            'role' => 'guru',
            'email' => 'owner.wali.' . $suffix . '@mail.com',
            'password' => 'password',
        ]);
        $second = User::create([
            'school_id' => $wakakur->school_id,
            'name' => 'Wali Kedua ' . $suffix,
            'role' => 'guru',
            'email' => 'second.wali.' . $suffix . '@mail.com',
            'password' => 'password',
        ]);
        $class = \App\Models\ClassRoom::create([
            'school_id' => $wakakur->school_id,
            'class_name' => 'StealTest ' . $suffix,
            'grade_level' => '7',
        ]);
        \App\Models\ClassHomeroom::create([
            'school_id' => $wakakur->school_id,
            'class_id' => $class->id,
            'user_id' => $owner->id,
            'sort' => 1,
        ]);
        \App\Models\ClassHomeroom::create([
            'school_id' => $wakakur->school_id,
            'class_id' => $class->id,
            'user_id' => $second->id,
            'sort' => 2,
        ]);

        try {
            $this->put('/wakasek/guru/' . $guru->id . '/wali', ['class_id' => $class->id])
                ->assertRedirect()
                ->assertSessionHasErrors('class_id');

            $this->assertDatabaseMissing('class_homerooms', [
                'class_id' => $class->id,
                'user_id' => $guru->id,
            ]);
        } finally {
            $class->delete();
            $owner->delete();
            $second->delete();
        }
    }

    public function test_plotting_store_duplicate_and_destroy(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $this->actingAs($wakakur);

        $suffix = now()->timestamp;
        $class = \App\Models\ClassRoom::create([
            'school_id' => $wakakur->school_id,
            'class_name' => 'PlotTest ' . $suffix,
            'grade_level' => '8',
        ]);
        $subject = \App\Models\Subject::create([
            'school_id' => $wakakur->school_id,
            'name' => 'PlotMapel ' . $suffix,
            'type' => \App\Models\Subject::TYPE_GENERAL,
        ]);

        try {
            $this->post('/wakasek/guru/' . $guru->id . '/plotting', [
                'class_ids' => [$class->id],
                'subject_id' => $subject->id,
            ])->assertRedirect()->assertSessionHas('status');

            $this->assertDatabaseHas('class_subject_teacher', [
                'school_id' => $wakakur->school_id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $guru->id,
            ]);

            $this->post('/wakasek/guru/' . $guru->id . '/plotting', [
                'class_ids' => [$class->id],
                'subject_id' => $subject->id,
            ])->assertRedirect()->assertSessionHasErrors('class_ids');

            $row = \App\Models\ClassSubjectTeacher::where('school_id', $wakakur->school_id)
                ->where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->firstOrFail();

            $this->delete('/wakasek/guru/' . $guru->id . '/plotting/' . $row->id)
                ->assertRedirect()
                ->assertSessionHas('status');

            $this->assertDatabaseMissing('class_subject_teacher', ['id' => $row->id]);
        } finally {
            \App\Models\ClassSubjectTeacher::where('class_id', $class->id)->where('subject_id', $subject->id)->delete();
            $subject->delete();
            $class->delete();
        }
    }

    public function test_plotting_rejects_foreign_class_subject(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $this->actingAs($wakakur);

        $suffix = now()->timestamp;
        $foreignSchool = \App\Models\School::create([
            'name' => 'Sekolah Lain Plot ' . $suffix,
            'slug' => 'sekolah-lain-plot-' . $suffix,
        ]);
        $foreignSubject = \App\Models\Subject::create([
            'school_id' => $foreignSchool->id,
            'name' => 'Mapel Asing ' . $suffix,
            'type' => \App\Models\Subject::TYPE_GENERAL,
        ]);

        try {
            $class = \App\Models\ClassRoom::create([
                'school_id' => $wakakur->school_id,
                'class_name' => 'Kelas Lokal Plot ' . $suffix,
                'grade_level' => '7',
            ]);

            $this->post('/wakasek/guru/' . $guru->id . '/plotting', [
                'class_ids' => [$class->id],
                'subject_id' => $foreignSubject->id,
            ])->assertRedirect()->assertSessionHasErrors('class_ids');

            $this->assertDatabaseMissing('class_subject_teacher', ['subject_id' => $foreignSubject->id]);

            $class->delete();
        } finally {
            $foreignSubject->delete();
            $foreignSchool->delete();
        }
    }

    public function test_priority_subjects_sync(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $this->actingAs($wakakur);

        $suffix = now()->timestamp;
        $subjectA = \App\Models\Subject::create([
            'school_id' => $wakakur->school_id,
            'name' => 'PrioritasA ' . $suffix,
            'type' => \App\Models\Subject::TYPE_GENERAL,
        ]);
        $subjectB = \App\Models\Subject::create([
            'school_id' => $wakakur->school_id,
            'name' => 'PrioritasB ' . $suffix,
            'type' => \App\Models\Subject::TYPE_GENERAL,
        ]);

        try {
            $this->put('/wakasek/guru/' . $guru->id . '/prioritas', [
                'subject_ids' => [$subjectA->id, $subjectB->id],
            ])->assertRedirect()->assertSessionHas('status');

            $this->assertCount(2, $guru->fresh()->subjects()->whereIn('subjects.id', [$subjectA->id, $subjectB->id])->get());

            $this->put('/wakasek/guru/' . $guru->id . '/prioritas', ['subject_ids' => []])
                ->assertRedirect()->assertSessionHas('status');

            $this->assertCount(0, $guru->fresh()->subjects()->whereIn('subjects.id', [$subjectA->id, $subjectB->id])->get());
        } finally {
            \DB::table('teacher_subject')->whereIn('subject_id', [$subjectA->id, $subjectB->id])->delete();
            $subjectA->delete();
            $subjectB->delete();
        }
    }

    public function test_teacher_role_create_and_destroy(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $this->actingAs($wakakur);

        $roleName = 'PJ Uji ' . now()->timestamp;

        $this->post('/wakasek/guru/' . $guru->id . '/tugas', [
            'role_name' => $roleName,
            'is_student_related' => '1',
        ])->assertRedirect()->assertSessionHas('status');

        $role = \App\Models\TeacherRole::where('school_id', $wakakur->school_id)
            ->where('teacher_id', $guru->id)
            ->where('role_name', $roleName)
            ->firstOrFail();

        $this->assertTrue((bool) $role->is_student_related);

        $this->delete('/wakasek/guru/' . $guru->id . '/tugas/' . $role->id)
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('teacher_roles', ['id' => $role->id]);
    }

    public function test_plotting_multiple_classes_selected(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $this->actingAs($wakakur);

        $suffix = now()->timestamp;
        $classes = collect();
        for ($i = 1; $i <= 3; $i++) {
            $classes->push(\App\Models\ClassRoom::create([
                'school_id' => $wakakur->school_id,
                'class_name' => 'SemuaKelas ' . $suffix . '-' . $i,
                'grade_level' => '7',
            ]));
        }
        $subject = \App\Models\Subject::create([
            'school_id' => $wakakur->school_id,
            'name' => 'SemuaMapel ' . $suffix,
            'type' => \App\Models\Subject::TYPE_GENERAL,
        ]);

        try {
            $this->post('/wakasek/guru/' . $guru->id . '/plotting', [
                'class_ids' => $classes->pluck('id')->all(),
                'subject_id' => $subject->id,
            ])->assertRedirect()->assertSessionHas('status');

            $count = \App\Models\ClassSubjectTeacher::where('school_id', $wakakur->school_id)
                ->where('subject_id', $subject->id)
                ->where('teacher_id', $guru->id)
                ->count();
            $this->assertSame(3, $count);

            $this->post('/wakasek/guru/' . $guru->id . '/plotting', [
                'class_ids' => $classes->pluck('id')->all(),
                'subject_id' => $subject->id,
            ])->assertRedirect()->assertSessionHasErrors('class_ids')->assertSessionHas('error');

            $this->assertSame(3, \App\Models\ClassSubjectTeacher::where('school_id', $wakakur->school_id)
                ->where('subject_id', $subject->id)
                ->where('teacher_id', $guru->id)
                ->count());
        } finally {
            \App\Models\ClassSubjectTeacher::where('subject_id', $subject->id)->delete();
            $subject->delete();
            foreach ($classes as $class) {
                $class->delete();
            }
        }
    }

    public function test_plotting_batch_destroy_selected(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $this->actingAs($wakakur);

        $suffix = now()->timestamp;
        $classes = collect();
        $rows = collect();
        try {
            for ($i = 1; $i <= 2; $i++) {
                $class = \App\Models\ClassRoom::create([
                    'school_id' => $wakakur->school_id,
                    'class_name' => 'BatchPlot ' . $suffix . '-' . $i,
                    'grade_level' => '8',
                ]);
                $classes->push($class);

                $subject = \App\Models\Subject::create([
                    'school_id' => $wakakur->school_id,
                    'name' => 'BatchMapel ' . $suffix . '-' . $i,
                    'type' => \App\Models\Subject::TYPE_GENERAL,
                ]);

                $row = \App\Models\ClassSubjectTeacher::create([
                    'school_id' => $wakakur->school_id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $guru->id,
                ]);
                $rows->push($row);
            }

            $this->delete('/wakasek/guru/' . $guru->id . '/plotting', [
                'plot_ids' => $rows->pluck('id')->all(),
            ])->assertRedirect()->assertSessionHas('status');

            foreach ($rows as $row) {
                $this->assertDatabaseMissing('class_subject_teacher', ['id' => $row->id]);
            }
        } finally {
            \App\Models\ClassSubjectTeacher::where('teacher_id', $guru->id)
                ->whereIn('id', $rows->pluck('id')->all())->delete();
            foreach ($rows as $row) {
                $row->delete();
            }
            foreach ($classes as $class) {
                $class->delete();
            }
        }
    }

    public function test_empty_task_cards_hidden_with_setup_button(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $this->actingAs($wakakur);

        $suffix = now()->timestamp;
        $guru = User::create([
            'school_id' => $wakakur->school_id,
            'name' => 'Guru Kosong ' . $suffix,
            'role' => 'guru',
            'email' => 'kosong.' . $suffix . '@mail.com',
            'password' => 'password',
        ]);

        try {
            $this->get('/wakasek/guru/' . $guru->id)
                ->assertOk()
                ->assertSee('Atur Tugas', false)
                ->assertSee('Plotting Mengajar', false)
                ->assertSee('Wali Kelas', false)
                ->assertSee('Mapel Prioritas', false)
                ->assertSee('Tugas Khusus / PJ', false)
                ->assertSee('Penugasan Tahfidz', false);
        } finally {
            $guru->delete();
        }
    }

    public function test_full_task_cards_visible_without_setup_button(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $suffix = now()->timestamp;

        $class = \App\Models\ClassRoom::create([
            'school_id' => $wakakur->school_id,
            'class_name' => 'FullTugas ' . $suffix,
            'grade_level' => '7',
        ]);
        \App\Models\ClassHomeroom::create([
            'school_id' => $wakakur->school_id,
            'class_id' => $class->id,
            'user_id' => $guru->id,
            'sort' => 1,
        ]);
        $subject = \App\Models\Subject::create([
            'school_id' => $wakakur->school_id,
            'name' => 'FullMapel ' . $suffix,
            'type' => \App\Models\Subject::TYPE_GENERAL,
        ]);
        $priority = \App\Models\TeacherSubject::create([
            'school_id' => $wakakur->school_id,
            'teacher_id' => $guru->id,
            'subject_id' => $subject->id,
        ]);
        $role = \App\Models\TeacherRole::create([
            'school_id' => $wakakur->school_id,
            'teacher_id' => $guru->id,
            'role_name' => 'FullTugas ' . $suffix,
            'is_student_related' => false,
        ]);
        $plotting = \App\Models\ClassSubjectTeacher::create([
            'school_id' => $wakakur->school_id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $guru->id,
        ]);

        try {
            $guru->update(['is_pj_tahfidz' => true]);

            $this->actingAs($wakakur);

            $this->get('/wakasek/guru/' . $guru->id)
                ->assertOk()
                ->assertDontSee('Atur Tugas', false);
        } finally {
            $guru->update(['is_pj_tahfidz' => false]);
            $plotting->delete();
            $priority->delete();
            $role->delete();
            $subject->delete();
            $class->delete();
        }
    }

    public function test_tahfidz_card_lists_mentored_students_and_classes(): void
    {
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $guru = User::where('email', 'guru@sit.sch.id')->where('school_id', $wakakur->school_id)->firstOrFail();
        $suffix = now()->timestamp;

        $class = \App\Models\ClassRoom::create([
            'school_id' => $wakakur->school_id,
            'class_name' => 'TahfidzKls ' . $suffix,
            'grade_level' => '7',
        ]);
        $student = \App\Models\Student::create([
            'school_id' => $wakakur->school_id,
            'name' => 'Murid Bina ' . $suffix,
            'class_id' => $class->id,
        ]);
        $assignment = \App\Models\QuranTeachingAssignment::create([
            'school_id' => $wakakur->school_id,
            'student_id' => $student->id,
            'teacher_id' => $guru->id,
        ]);

        try {
            $guru->update(['is_pj_tahfidz' => true]);

            $this->actingAs($wakakur);

            $this->get('/wakasek/guru/' . $guru->id)
                ->assertOk()
                ->assertSee('Penugasan Tahfidz', false)
                ->assertSee('Murid Bina ' . $suffix, false)
                ->assertSee('TahfidzKls ' . $suffix, false);
        } finally {
            $guru->update(['is_pj_tahfidz' => false]);
            $assignment->delete();
            $student->delete();
            $class->delete();
        }
    }

    public function test_guru_role_cannot_access_assignment_routes(): void
    {
        $guru = User::where('email', 'guru@sit.sch.id')->firstOrFail();
        $this->actingAs($guru);

        $suffix = now()->timestamp;
        $foreign = null;
        try {
            $foreign = User::where('role', 'guru')->where('school_id', $guru->school_id)
                ->where('id', '<>', $guru->id)->first()
                ?? User::create([
                    'school_id' => $guru->school_id,
                    'name' => 'Target ' . $suffix,
                    'role' => 'guru',
                    'email' => 'target.guru.' . $suffix . '@mail.com',
                    'password' => 'password',
                ]);

            $this->get('/wakasek/guru')->assertForbidden();
            $this->put('/wakasek/guru/' . $foreign->id . '/wali')->assertForbidden();
            $this->post('/wakasek/guru/' . $foreign->id . '/plotting')->assertForbidden();
            $this->put('/wakasek/guru/' . $foreign->id . '/prioritas')->assertForbidden();
            $this->post('/wakasek/guru/' . $foreign->id . '/tugas')->assertForbidden();
        } finally {
            if ($foreign && str_starts_with($foreign->email, 'target.guru.')) {
                $foreign->delete();
            }
        }
    }

    public function test_wakasek_can_access_quran_assignment_page(): void
    {
        $wakasek = User::where('email', 'wakamur@sit.sch.id')->firstOrFail();
        $this->actingAs($wakasek);

        $this->get('/wakasek/quran-assignment')
            ->assertOk()
            ->assertSee('Assignment Guru & Siswa Al-Quran', false);
    }

    public function test_wakasek_quran_assignment_store_redirects_to_wakasek_index(): void
    {
        $wakasek = User::where('email', 'wakamur@sit.sch.id')->firstOrFail();
        $this->actingAs($wakasek);

        $this->post('/wakasek/quran-assignment', [
            'student_id' => (string) \Illuminate\Support\Str::uuid(),
            'teacher_id' => (string) \Illuminate\Support\Str::uuid(),
        ])->assertRedirect(route('wakasek.quran-assignment.index'));
    }
}