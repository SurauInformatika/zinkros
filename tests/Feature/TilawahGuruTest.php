<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\QuranReadingLevel;
use App\Models\QuranTeachingAssignment;
use App\Models\School;
use App\Models\Student;
use App\Models\TilawahRecord;
use App\Models\User;
use Tests\TestCase;

class TilawahGuruTest extends TestCase
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

    public function test_full_tilawah_module_for_guru(): void
    {
        $ts = now()->timestamp;
        $school = School::where('slug', 'sit-default')->firstOrFail();

        $class = ClassRoom::create(['school_id' => $school->id, 'class_name' => 'TilKls ' . $ts, 'grade_level' => '7']);
        $student = Student::create([
            'school_id' => $school->id,
            'name' => 'Siswa Tilawah ' . $ts,
            'gender' => 'L',
            'nis' => 'NIS-TIL-' . $ts,
            'class_id' => $class->id,
        ]);
        $teacher = User::create([
            'school_id' => $school->id,
            'name' => 'Guru Tilawah ' . $ts,
            'role' => 'guru',
            'email' => 'guru-tilawah-' . $ts . '@test.id',
            'password' => bcrypt('password'),
        ]);

        QuranTeachingAssignment::create([
            'school_id' => $school->id,
            'academic_year_id' => null,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
        ]);

        try {
            $this->actingAs($teacher);

            $this->get('/guru/quran-tilawah')
                ->assertOk()
                ->assertSee('Input Tilawah Al-Quran', false);

            $this->get('/guru/quran-tilawah/input/' . $student->id)
                ->assertOk()
                ->assertSee('Form Input Tilawah', false);

            $level = QuranReadingLevel::where('kind', 'JILID')->where('number', 1)->firstOrFail();
            $this->post('/guru/quran-tilawah', [
                'student_id' => $student->id,
                'recorded_date' => now()->toDateString(),
                'reading_level_id' => $level->id,
                'page_start' => 1,
                'page_end' => 2,
                'score' => 85,
                'notes' => 'uji',
            ])->assertRedirect(route('guru.quran-tilawah.input', $student->id))
                ->assertSessionHas('success');

            $record = TilawahRecord::where('student_id', $student->id)->where('teacher_id', $teacher->id)->firstOrFail();
            $this->assertSame($level->id, $record->reading_level_id);
            $this->assertSame(2, $record->page_end);
            $this->assertNull($record->status);

            $this->post('/guru/quran-tilawah', [
                'student_id' => $student->id,
                'recorded_date' => now()->toDateString(),
                'status' => 'SAKIT',
                'notes' => 'sakit',
            ])->assertRedirect()->assertSessionHas('success');

            $this->get('/guru/quran-tilawah/history/' . $student->id)
                ->assertOk()
                ->assertSee('Riwayat Tilawah', false);

            $resp = $this->get('/guru/quran-tilawah/history/' . $student->id . '/chart?filter=ta_init');
            $resp->assertOk();
            $this->assertSame(2, $resp->json()['stats']['pages']);
        } finally {
            TilawahRecord::where('student_id', $student->id)->delete();
            QuranTeachingAssignment::where('student_id', $student->id)->delete();
            $student->delete();
            $class->delete();
            $teacher->delete();
        }
    }
}
