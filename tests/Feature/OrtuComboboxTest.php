<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\ClassRoom;
use Tests\TestCase;

class OrtuComboboxTest extends TestCase
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

    protected function admin(): User
    {
        // adhir@mail.com is admin in the school that has parents AND classes
        return User::where('email', 'adhir@mail.com')->firstOrFail();
    }

    public function test_search_ortu_endpoint(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        // create form renders with the combobox (JS) present
        $page = $this->get(route('admin.siswa.create'));
        $page->assertOk();
        $page->assertSee('Tambah Ortu / Wali', false);
        $page->assertSee('refreshRelationOptions', false);

        $resp = $this->getJson(route('admin.siswa.ortu.search') . '?q=Ibu Demo');
        $resp->assertOk();
        $data = $resp->json('data');
        echo "search 'Ibu Demo' found: " . count($data) . "\n";
        $this->assertNotEmpty($data);
        $this->assertEquals('Ibu Demo', $data[0]['name']);
        $this->assertArrayHasKey('children_count', $data[0]);
    }

    public function test_search_all_with_children_count(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $resp = $this->getJson(route('admin.siswa.ortu.search'));
        $resp->assertOk();
        $data = $resp->json('data');
        echo "search all found: " . count($data) . " (max 20)\n";
        $this->assertLessThanOrEqual(20, count($data));
    }

    public function test_store_ortu_endpoint(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $email = 'combobox_' . uniqid() . '@mail.com';
        $resp = $this->postJson(route('admin.siswa.ortu.store'), [
            'name' => 'Bpk. Test Combobox',
            'email' => $email,
            'phone' => '081234567' . substr(md5(uniqid()), 0, 6),
        ]);
        echo "store status: " . $resp->getStatusCode() . "\n";
        $resp->assertOk();
        $this->assertEquals('Bpk. Test Combobox', $resp->json('data.name'));
        $this->assertSame(0, $resp->json('data.children_count'));

        // Duplicate email should fail with 422
        $dup = $this->postJson(route('admin.siswa.ortu.store'), ['name' => 'x', 'email' => $email]);
        echo "duplicate status: " . $dup->getStatusCode() . "\n";
        $dup->assertStatus(422);

        // cleanup
        User::where('email', $email)->delete();
    }

    public function test_full_student_store_with_parent(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $schoolId = $admin->school_id;

        $class = ClassRoom::where('school_id', $schoolId)->firstOrFail();
        $parent = User::where('role', 'ortu')->where('school_id', $schoolId)->firstOrFail();

        $resp = $this->post(route('admin.siswa.store'), [
            'name' => 'Anak Combobox Test',
            'gender' => 'L',
            'nis' => 'CBT-' . uniqid(),
            'class_id' => $class->id,
            'parent_user_ids' => [$parent->id],
            'parent_relations' => ['AYAH'],
            'primary_parent' => '0',
        ]);

        $resp->assertRedirect();
        $student = Student::where('name', 'Anak Combobox Test')->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($student);
        $pivot = StudentParent::where('student_id', $student->id)->where('user_id', $parent->id)->first();
        $this->assertNotNull($pivot);
        echo "pivot id: " . $pivot->id . " relation: " . $pivot->relation . " primary: " . $pivot->is_primary . "\n";
        $this->assertNotEmpty($pivot->id);

        $student->delete();
    }

    public function test_rejects_two_ayah_for_same_student(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $schoolId = $admin->school_id;

        $class = ClassRoom::where('school_id', $schoolId)->firstOrFail();
        $parents = User::where('role', 'ortu')->where('school_id', $schoolId)->limit(2)->get();
        $this->assertGreaterThanOrEqual(2, $parents->count());

        $resp = $this->from(route('admin.siswa.create'))->post(route('admin.siswa.store'), [
            'name' => 'Anak Dua Ayah Test',
            'gender' => 'P',
            'nis' => 'DAT-' . uniqid(),
            'class_id' => $class->id,
            'parent_user_ids' => [$parents[0]->id, $parents[1]->id],
            'parent_relations' => ['AYAH', 'AYAH'],
            'primary_parent' => '0',
        ]);

        $resp->assertSessionHasErrors('parent_relations');
        echo "error: " . collect($resp->baseResponse->getSession()->get('errors')->get('parent_relations'))->implode('; ') . "\n";
        $this->assertDatabaseMissing('students', ['name' => 'Anak Dua Ayah Test']);
    }
}
