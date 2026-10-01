<?php

namespace Tests\Feature;

use App\Models\QuranReadingLevel;
use App\Models\School;
use App\Models\User;
use Tests\TestCase;

class ReadingLevelAdminTest extends TestCase
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

    public function test_admin_can_manage_school_reading_levels(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $snap = $this->snapshot($admin->school_id);

        $this->get(route('admin.reading-levels.index'))
            ->assertOk()
            ->assertSee('Jenjang Baca', false);

        $this->post(route('admin.reading-levels.store'), [
            'kind' => 'IQRA',
            'number' => 1,
            'label' => 'Iqra 1',
            'pages' => 40,
        ])->assertSessionHas('success');

        $level = QuranReadingLevel::where('school_id', $admin->school_id)
            ->where('kind', 'IQRA')
            ->where('number', 1)
            ->firstOrFail();

        try {
            $this->put(route('admin.reading-levels.update', $level), [
                'label' => 'Iqra 1 Revisi',
                'pages' => 42,
            ])->assertSessionHas('success');

            $level->refresh();
            $this->assertSame('Iqra 1 Revisi', $level->label);
            $this->assertSame(42, (int) $level->pages);

            $this->get(route('admin.reading-levels.index'))
                ->assertOk()
                ->assertSee('Iqra 1 Revisi', false);
        } finally {
            $this->restore($admin->school_id, $snap);
        }
    }

    public function test_school_owned_catalog_uses_only_school_levels(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $schoolId = $admin->school_id;

        $snap = $this->snapshot($schoolId);

        foreach (['JILID' => 1, 'JUZ' => 1] as $kind => $number) {
            QuranReadingLevel::firstOrCreate(
                ['school_id' => $schoolId, 'kind' => $kind, 'number' => $number],
                ['label' => $kind . ' ' . $number, 'pages' => 30],
            );
        }

        try {
            $catalog = QuranReadingLevel::forSchool($schoolId);
            $this->assertTrue($catalog->isNotEmpty());
            $this->assertTrue($catalog->every(fn ($l) => $l->school_id === $schoolId));
            $this->assertTrue($catalog->contains(fn ($l) => $l->kind === 'JILID' && $l->number === 1));

            $juz30 = QuranReadingLevel::whereNull('school_id')->where('kind', 'JUZ')->where('number', 30)->first();
            $this->assertNotNull($juz30);
            $this->assertFalse($catalog->contains(fn ($l) => $l->id === $juz30->id));
        } finally {
            $this->restore($schoolId, $snap);
        }
    }

    public function test_admin_can_bulk_delete_school_reading_levels(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $schoolId = $admin->school_id;

        $snap = $this->snapshot($schoolId);

        $a = QuranReadingLevel::create(['school_id' => $schoolId, 'kind' => 'IQRA', 'number' => 1, 'label' => 'Iqra 1', 'pages' => 40]);
        $b = QuranReadingLevel::create(['school_id' => $schoolId, 'kind' => 'IQRA', 'number' => 2, 'label' => 'Iqra 2', 'pages' => 40]);
        $c = QuranReadingLevel::create(['school_id' => $schoolId, 'kind' => 'UMMI', 'number' => 1, 'label' => 'UMMI 1', 'pages' => 20]);
        $global = QuranReadingLevel::whereNull('school_id')->where('kind', 'JILID')->orderBy('number')->first();

        try {
            $this->delete(route('admin.reading-levels.bulk-destroy'), ['ids' => [$a->id, $b->id, $c->id]])
                ->assertSessionHas('success');

            $this->assertNull(QuranReadingLevel::find($a->id));
            $this->assertNull(QuranReadingLevel::find($b->id));
            $this->assertNull(QuranReadingLevel::find($c->id));
            $this->assertNotNull(QuranReadingLevel::find($global->id));

            $this->get(route('admin.reading-levels.index'))
                ->assertOk()
                ->assertSee('Hapus Terpilih', false);
        } finally {
            $this->restore($schoolId, $snap);
        }
    }

    public function test_admin_can_bulk_delete_global_default_levels(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $schoolId = $admin->school_id;

        $snap = $this->snapshot($schoolId);

        $jilids = QuranReadingLevel::whereNull('school_id')->where('kind', 'JILID')->orderBy('number')->get();
        $this->assertGreaterThanOrEqual(2, $jilids->count());

        $ids = $jilids->take(2)->pluck('id')->all();

        try {
            $this->delete(route('admin.reading-levels.bulk-destroy'), ['ids' => $ids])
                ->assertSessionHas('success');

            $this->assertSame(2, QuranReadingLevel::whereNull('school_id')->whereIn('id', $ids)->count(), 'baris bawaan global tidak boleh ikut terhapus');

            $own = QuranReadingLevel::where('school_id', $schoolId)->get();
            $this->assertTrue($own->isNotEmpty());

            $deleteKinds = $jilids->take(2)->map(fn ($l) => [$l->kind, $l->number]);
            foreach ($deleteKinds as [$kind, $number]) {
                $this->assertNull($own->first(fn ($l) => $l->kind === $kind && $l->number === $number), "baris $kind $number seharusnya terhapus dari katalog sekolah");
            }

            $this->assertNotNull($own->first(fn ($l) => $l->kind === 'JILID' && $l->number === $jilids[2]->number));
        } finally {
            $this->restore($schoolId, $snap);
        }
    }

    private function snapshot(string $schoolId): array
    {
        return [
            'ids' => QuranReadingLevel::where('school_id', $schoolId)->pluck('id')->all(),
            'customized' => (bool) School::where('id', $schoolId)->value('reading_catalog_customized'),
        ];
    }

    private function restore(string $schoolId, array $snap): void
    {
        QuranReadingLevel::where('school_id', $schoolId)->whereNotIn('id', $snap['ids'])->delete();
        School::where('id', $schoolId)->update(['reading_catalog_customized' => $snap['customized']]);
    }
}