<?php

namespace Tests\Feature;

use App\Models\RaporTemplate;
use App\Models\User;
use Tests\TestCase;

class RaporDesignSmokeTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'sit_school']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);

        $this->user = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
    }

    public function test_index_renders(): void
    {
        $this->actingAs($this->user)
            ->get(route('wakasek.rapor-design.index'))
            ->assertOk()
            ->assertSee('Desain Format Rapor');
    }

    public function test_create_renders_editor(): void
    {
        $this->actingAs($this->user)
            ->get(route('wakasek.rapor-design.create'))
            ->assertOk()
            ->assertSee('Desain Rapor Baru')
            ->assertSee('paper-editor')
            ->assertSee('Tambah Blok');
    }

    public function test_editor_action_add_block(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => $blocks,
                'action' => 'add',
                'add_type' => 'catatan',
            ]);

        $resp->assertOk();
        $this->assertStringContainsString('Catatan Wali Kelas', $resp->json('paper'));
        $this->assertCount(count(\App\Support\RaporFormat::defaultBlocks()) + 1, $resp->json('blocks'));
    }

    public function test_editor_action_inline_table_controls(): void
    {
        $blocks = json_encode([
            ['id' => 'b1', 'type' => 'tabel-bebas', 'props' => ['title' => 'Kegiatan', 'columns' => [
                ['label' => 'Hari'], ['label' => 'Kegiatan'],
            ], 'rows' => 0]],
        ], JSON_UNESCAPED_UNICODE);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => $blocks,
                'action' => 'list-add',
                'target_id' => 'b1',
                'path' => 'columns',
            ]);
        $resp->assertOk();
        $this->assertCount(3, $resp->json('blocks.0.props.columns'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'prop-inc',
                'target_id' => 'b1',
                'path' => 'rows',
                'step' => 1,
            ]);
        $resp->assertOk();
        $this->assertSame(1, $resp->json('blocks.0.props.rows'));
        $this->assertStringContainsString('Tambah Baris', $resp->json('paper'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'set',
                'target_id' => 'b1',
                'path' => 'rows',
                'set' => 5,
            ]);
        $resp->assertOk();
        $this->assertSame(5, $resp->json('blocks.0.props.rows'));
    }

    public function test_kop_inline_editor_actions(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);
        $schoolName = $this->user->school->name;

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => $blocks,
                'action' => 'title-add',
                'target_id' => 'b1',
            ]);
        $resp->assertOk();
        $this->assertCount(2, $resp->json('blocks.0.props.title_lines'));
        $this->assertSame($schoolName, $resp->json('blocks.0.props.title_lines.0'));
        $this->assertSame('', $resp->json('blocks.0.props.title_lines.1'));
        $this->assertSame([15, 15], $resp->json('blocks.0.props.title_sizes'));
        $this->assertStringContainsString('Judul Kop', $resp->json('paper'));
        $this->assertStringContainsString('data-act="title-size"', $resp->json('paper'));
        $this->assertStringContainsString('path="props.title_sizes.0"', $resp->json('paper'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'title-size',
                'target_id' => 'b1',
                'idx' => 1,
                'dir' => 'up',
            ]);
        $resp->assertOk();
        $this->assertSame([15, 16], $resp->json('blocks.0.props.title_sizes'));
        $this->assertStringContainsString('font-size: 16px;', $resp->json('paper'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'title-add',
                'target_id' => 'b1',
            ]);
        $resp->assertOk();
        $this->assertCount(3, $resp->json('blocks.0.props.title_lines'));
        $this->assertSame([15, 16, 15], $resp->json('blocks.0.props.title_sizes'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'title-move',
                'target_id' => 'b1',
                'idx' => 0,
                'dir' => 'down',
            ]);
        $resp->assertOk();
        $this->assertSame('', $resp->json('blocks.0.props.title_lines.0'));
        $this->assertSame($schoolName, $resp->json('blocks.0.props.title_lines.1'));
        $this->assertSame([16, 15, 15], $resp->json('blocks.0.props.title_sizes'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'title-move',
                'target_id' => 'b1',
                'idx' => 1,
                'dir' => 'up',
            ]);
        $resp->assertOk();
        $this->assertSame($schoolName, $resp->json('blocks.0.props.title_lines.0'));
        $this->assertSame([15, 16, 15], $resp->json('blocks.0.props.title_sizes'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'title-del',
                'target_id' => 'b1',
                'idx' => 2,
            ]);
        $resp->assertOk();
        $this->assertCount(2, $resp->json('blocks.0.props.title_lines'));
        $this->assertSame([15, 16], $resp->json('blocks.0.props.title_sizes'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'title-del',
                'target_id' => 'b1',
                'idx' => 1,
            ]);
        $resp->assertOk();
        $this->assertCount(1, $resp->json('blocks.0.props.title_lines'));
        $this->assertSame([15], $resp->json('blocks.0.props.title_sizes'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'title-del',
                'target_id' => 'b1',
                'idx' => 0,
            ]);
        $resp->assertOk();
        $this->assertSame([], $resp->json('blocks.0.props.title_lines'));
        $this->assertSame([], $resp->json('blocks.0.props.title_sizes'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'prop-inc',
                'target_id' => 'b1',
                'path' => 'title_font_size',
                'step' => 3,
            ]);
        $resp->assertOk();
        $this->assertSame(18, $resp->json('blocks.0.props.title_font_size'));
        $this->assertStringContainsString('contenteditable', $resp->json('paper'));
    }

    public function test_kop_edit_renders_default_virtual_lines(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.preview'), [
                'blocks' => $blocks,
                'semester' => 1,
            ]);

        $resp->assertOk();
        $paper = implode('', $resp->json('slots'));
        $this->assertStringContainsString('data-edit-title="b1"', $paper);
        $this->assertStringContainsString('data-virtual="1"', $paper);
        $this->assertStringContainsString(htmlspecialchars($this->user->school->name), $paper);
        $this->assertStringContainsString('Judul Kop', $paper);
        $this->assertStringContainsString('contenteditable', $paper);
    }

    public function test_judul_inline_font_size_controls(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => $blocks,
                'action' => 'prop-inc',
                'target_id' => 'b2',
                'path' => 'font_size',
                'step' => 1,
            ]);

        $resp->assertOk();
        $this->assertSame(17, $resp->json('blocks.1.props.font_size'));
        $paper = $resp->json('paper');
        $this->assertStringContainsString('data-edit-title="b2"', $paper);
        $this->assertStringContainsString('path="props.lines.0"', $paper);
        $this->assertStringContainsString('path="props.line_sizes.0"', $paper);
        $this->assertStringContainsString('path="props.font_size"', $paper);
        $this->assertStringContainsString('path="props.semester_size"', $paper);
        $this->assertStringContainsString('font-size: 17px;', $paper);
        $this->assertStringContainsString('LAPORAN HASIL BELAJAR PESERTA DIDIK', $paper);

        $blocksAfter = $resp->json('blocks');
        $blocksAfter[1]['props']['lines'] = ['LAPORAN PERKEMBANGAN BELAJAR SISWA'];

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($blocksAfter, JSON_UNESCAPED_UNICODE),
                'action' => 'prop-inc',
                'target_id' => 'b2',
                'path' => 'font_size',
                'step' => 0,
            ]);
        $resp->assertOk();
        $this->assertSame(['LAPORAN PERKEMBANGAN BELAJAR SISWA'], $resp->json('blocks.1.props.lines'));
        $this->assertStringContainsString('LAPORAN PERKEMBANGAN BELAJAR SISWA', $resp->json('paper'));
    }

    public function test_judul_deletions_persist_through_store(): void
    {
        $blocks = \App\Support\RaporFormat::defaultBlocks();
        $blocks[1]['props']['lines'] = [];
        $blocks[1]['props']['line_sizes'] = [];
        $blocks[1]['props']['show_semester'] = 0;
        $name = 'Judul Kosong '.uniqid();

        $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.store'), [
                'name' => $name,
                'blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE),
            ])->assertRedirect();

        $template = RaporTemplate::where('school_id', $this->user->school_id)
            ->where('name', $name)->first();

        try {
            $this->assertNotNull($template);
            $saved = $template->blocks;
            $this->assertSame([], $saved[1]['props']['lines']);
            $this->assertSame(0, $saved[1]['props']['show_semester']);

            $resp = $this->actingAs($this->user)
                ->get(route('wakasek.rapor-design.edit', $template->id))
                ->assertOk();
            $resp->assertDontSee('path="props.lines.0"', false);
            $resp->assertDontSee('data-edit-key="semester_text"', false);
            $resp->assertDontSee('TAHUN PELAJARAN');
        } finally {
            $template?->delete();
        }
    }

    public function test_judul_semester_line_editable_and_deletable(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => $blocks,
                'action' => 'set',
                'target_id' => 'b2',
                'path' => 'show_semester',
                'set' => '1',
            ]);
        $resp->assertOk();
        $paper = $resp->json('paper');
        $this->assertStringContainsString('data-edit-key="semester_text"', $paper);
        $this->assertStringContainsString('path="props.semester_text"', $paper);
        $this->assertStringContainsString('data-act="set"', $paper);
        $this->assertStringContainsString('data-path="show_semester"', $paper);
        $this->assertStringContainsString('TAHUN PELAJARAN', $paper);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => $blocks,
                'action' => 'set',
                'target_id' => 'b2',
                'path' => 'show_semester',
                'set' => '0',
            ]);
        $resp->assertOk();
        $this->assertSame(0, $resp->json('blocks.1.props.show_semester'));
        $this->assertStringNotContainsString('TAHUN PELAJARAN', $resp->json('paper'));
        $this->assertStringNotContainsString('SEMESTER', $resp->json('paper'));
    }

    public function test_judul_multiline_actions(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);

        $post = fn (string $blocksJson, string $action, array $extra = []) => $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => $blocksJson,
                'action' => $action,
                'target_id' => 'b2',
            ] + $extra);

        $resp = $post($blocks, 'title-add')->assertOk();
        $this->assertSame(['LAPORAN HASIL BELAJAR PESERTA DIDIK', ''], $resp->json('blocks.1.props.lines'));
        $this->assertSame([16, 16], $resp->json('blocks.1.props.line_sizes'));
        $blocks = json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE);

        $resp = $post($blocks, 'title-move', ['idx' => 0, 'dir' => 'down'])->assertOk();
        $this->assertSame(['', 'LAPORAN HASIL BELAJAR PESERTA DIDIK'], $resp->json('blocks.1.props.lines'));
        $blocks = json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE);

        $resp = $post($blocks, 'title-size', ['idx' => 1, 'dir' => 'up'])->assertOk();
        $this->assertSame(['', 'LAPORAN HASIL BELAJAR PESERTA DIDIK'], $resp->json('blocks.1.props.lines'));
        $this->assertSame([16, 17], $resp->json('blocks.1.props.line_sizes'));
        $blocks = json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE);

        $resp = $post($blocks, 'title-del', ['idx' => 0])->assertOk();
        $this->assertSame(['LAPORAN HASIL BELAJAR PESERTA DIDIK'], $resp->json('blocks.1.props.lines'));
        $this->assertSame([17], $resp->json('blocks.1.props.line_sizes'));
        $this->assertStringContainsString('data-act="title-del"', $resp->json('paper'));
        $this->assertStringContainsString('data-act="title-move"', $resp->json('paper'));
        $this->assertStringContainsString('+ Baris Judul', $resp->json('paper'));
    }

    public function test_kop_upload_logo(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->image('logo-sekolah.png', 86, 86);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.upload-logo'), ['file' => $file]);

        $resp->assertOk();
        $path = $resp->json('path');
        $this->assertNotNull($path);
        $this->assertStringStartsWith('rapor/rapor-kop-', $path);
        $this->assertStringContainsString('storage/'.$path, $resp->json('url'));

        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
    }

    public function test_kop_title_lines_persist_on_save(): void
    {
        $blocks = [
            ['id' => 'b1', 'type' => 'kop', 'props' => [
                'show_logo' => true, 'show_address' => true, 'show_contact' => true,
                'logo' => 'rapor/kop-test.png',
                'title_lines' => ['YAYASAN PENDIDIKAN', 'SMP NEGERI 1 CONTOH', 'Jl. Jendral Sudirman No. 123'],
                'title_sizes' => [20, 16, 12],
                'title_font_size' => 18,
            ]],
            ['id' => 'b2', 'type' => 'teks-bebas', 'props' => ['text' => 'x']],
        ];
        $name = 'Kop Test '.uniqid();

        $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.store'), [
                'name' => $name,
                'blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE),
            ])->assertRedirect();

        $template = RaporTemplate::where('school_id', $this->user->school_id)
            ->where('name', $name)->first();

        try {
            $this->assertNotNull($template);
            $saved = $template->blocks;
            $this->assertSame(3, count($saved[0]['props']['title_lines']));
            $this->assertSame('SMP NEGERI 1 CONTOH', $saved[0]['props']['title_lines'][1]);
            $this->assertSame([20, 16, 12], $saved[0]['props']['title_sizes']);
            $this->assertSame(18, $saved[0]['props']['title_font_size']);
            $this->assertSame('rapor/kop-test.png', $saved[0]['props']['logo']);
        } finally {
            if ($template) {
                $template->delete();
            }
        }
    }

    public function test_preview_returns_slots(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.preview'), ['blocks' => $blocks, 'semester' => 1]);

        $resp->assertOk();
        $data = $resp->json();
        $this->assertArrayHasKey('slots', $data);
        $this->assertNotEmpty($data['slots']);
        $this->assertStringContainsString('LAPORAN HASIL BELAJAR PESERTA DIDIK', implode('', $data['slots']));
    }

    public function test_identitas_two_column_management(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => $blocks,
                'action' => 'list-add',
                'target_id' => 'b3',
                'path' => 'left',
            ]);
        $resp->assertOk();
        $this->assertCount(4, $resp->json('blocks.2.props.left'));
        $this->assertSame('Baris Baru', $resp->json('blocks.2.props.left.3.label'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'col-move',
                'target_id' => 'b3',
                'path' => 'left',
                'idx' => 0,
            ]);
        $resp->assertOk();
        $this->assertCount(3, $resp->json('blocks.2.props.left'));
        $this->assertCount(4, $resp->json('blocks.2.props.right'));
        $this->assertSame('Nama Lengkap', $resp->json('blocks.2.props.right.3.label'));

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE),
                'action' => 'list-del',
                'target_id' => 'b3',
                'path' => 'left',
                'idx' => 2,
            ]);
        $resp->assertOk();
        $this->assertCount(2, $resp->json('blocks.2.props.left'));

        $paper = $resp->json('paper');
        $this->assertStringContainsString('class="id-col"', $paper);
        $this->assertStringContainsString('data-act="col-move"', $paper);
        $this->assertStringContainsString('data-path="left"', $paper);
        $this->assertStringContainsString('+ Baris Kiri', $paper);
        $this->assertStringContainsString('+ Baris Kanan', $paper);
        $this->assertStringContainsString('data-edit-label="b3"', $paper);
        $this->assertStringContainsString('data-label-path="props.left.0.label"', $paper);
        $this->assertStringContainsString('data-label-path="props.right.2.label"', $paper);
    }

    public function test_identitas_label_edit_persists(): void
    {
        $blocks = \App\Support\RaporFormat::defaultBlocks();
        $blocks[2]['props']['left'][1]['label'] = 'NIM / NISN';
        $blocks[2]['props']['right'][2]['label'] = 'Tahun Ajaran';
        $name = 'Identitas Label '.uniqid();

        $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.store'), [
                'name' => $name,
                'blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE),
            ])->assertRedirect();

        $template = RaporTemplate::where('school_id', $this->user->school_id)
            ->where('name', $name)->first();

        try {
            $this->assertNotNull($template);
            $this->assertSame('NIM / NISN', $template->blocks[2]['props']['left'][1]['label']);
            $this->assertSame('Tahun Ajaran', $template->blocks[2]['props']['right'][2]['label']);

            $resp = $this->actingAs($this->user)
                ->get(route('wakasek.rapor-design.edit', $template->id))
                ->assertOk();
            $html = $resp->getContent();
            $this->assertStringContainsString('NIM / NISN', $html);
            $this->assertStringContainsString('Tahun Ajaran', $html);
            $this->assertStringContainsString('data-edit-label', $html);
            $this->assertStringContainsString('data-label-path="props.left.1.label"', $html);
        } finally {
            $template?->delete();
        }
    }

    public function test_identitas_row_move_actions(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);

        $post = fn (string $blocksJson, string $action, array $extra = []) => $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'blocks' => $blocksJson,
                'action' => $action,
                'target_id' => 'b3',
            ] + $extra);

        $resp = $post($blocks, 'list-move', ['path' => 'left', 'idx' => 0, 'dir' => 'down'])->assertOk();
        $this->assertSame('NISN / NIS', $resp->json('blocks.2.props.left.0.label'));
        $this->assertSame('Nama Lengkap', $resp->json('blocks.2.props.left.1.label'));
        $blocks = json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE);

        $resp = $post($blocks, 'list-move', ['path' => 'left', 'idx' => 1, 'dir' => 'up'])->assertOk();
        $this->assertSame('Nama Lengkap', $resp->json('blocks.2.props.left.0.label'));
        $this->assertSame('NISN / NIS', $resp->json('blocks.2.props.left.1.label'));

        $resp = $post($blocks, 'list-move', ['path' => 'right', 'idx' => 2, 'dir' => 'down'])->assertOk();
        $this->assertSame('Tahun Pelajaran', $resp->json('blocks.2.props.right.2.label'));
        $this->assertSame(3, count($resp->json('blocks.2.props.right')));

        $resp = $post(json_encode($resp->json('blocks'), JSON_UNESCAPED_UNICODE), 'col-move', ['path' => 'right', 'idx' => 2])->assertOk();
        $this->assertCount(2, $resp->json('blocks.2.props.right'));
        $this->assertCount(4, $resp->json('blocks.2.props.left'));
        $this->assertSame('Tahun Pelajaran', $resp->json('blocks.2.props.left.3.label'));

        $paper = $resp->json('paper');
        $this->assertStringContainsString('data-act="list-move"', $paper);
        $this->assertStringContainsString('data-act="col-move"', $paper);
        $this->assertStringContainsString('data-drag-handle', $paper);
        $this->assertStringContainsString('id="id-left"', $paper);
        $this->assertStringContainsString('id="id-right"', $paper);
        $this->assertStringContainsString('title="Pindah ke kolom kiri"', $paper);
        $this->assertStringContainsString('title="Pindah ke kolom kanan"', $paper);
    }

    public function test_identitas_reorder_action_rerenders_paper(): void
    {
        $blocks = \App\Support\RaporFormat::defaultBlocks();
        $blocks[2]['props']['left'] = array_values([
            $blocks[2]['props']['left'][1],
            $blocks[2]['props']['left'][0],
            $blocks[2]['props']['left'][2],
        ]);

        $resp = $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.editor'), [
                'action' => 'reorder',
                'target_id' => 'b3',
                'path' => '',
                'idx' => '',
                'blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE),
            ])->assertOk();

        $this->assertSame('NISN / NIS', $resp->json('blocks.2.props.left.0.label'));
        $this->assertSame('Nama Lengkap', $resp->json('blocks.2.props.left.1.label'));
        $this->assertStringContainsString('data-edit-label="b3"', $resp->json('paper'));
        $this->assertStringContainsString('data-label-path="props.left.0.label"', $resp->json('paper'));
    }

    public function test_identitas_legacy_rows_migrates_to_left_right(): void
    {
        $legacy = [
            ['label' => 'Nama Lengkap', 'key' => 'name'],
            ['label' => 'NISN / NIS', 'key' => 'nisn_nis'],
            ['label' => 'Jenis Kelamin', 'key' => 'gender'],
            ['label' => 'Nama Sekolah', 'key' => 'school_name'],
            ['label' => 'Kelas / Semester', 'key' => 'class_semester'],
            ['label' => 'Tahun Pelajaran', 'key' => 'tahun'],
        ];

        $blocks = \App\Support\RaporFormat::normalize([
            ['id' => 'b1', 'type' => 'identitas', 'props' => ['rows' => $legacy]],
        ]);

        $props = $blocks[0]['props'];
        $this->assertArrayNotHasKey('rows', $props);
        $this->assertCount(3, $props['left']);
        $this->assertCount(3, $props['right']);
        $this->assertSame('Nama Lengkap', $props['left'][0]['label']);
        $this->assertSame('Tahun Pelajaran', $props['right'][2]['label']);

        $name = 'Identitas Legacy '.uniqid();
        $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.store'), [
                'name' => $name,
                'blocks' => json_encode(
                    [['id' => 'b1', 'type' => 'identitas', 'props' => ['rows' => $legacy]]],
                    JSON_UNESCAPED_UNICODE
                ),
            ])->assertRedirect();

        $template = RaporTemplate::where('school_id', $this->user->school_id)
            ->where('name', $name)->first();

        try {
            $this->assertNotNull($template);
            $saved = $template->blocks;
            $this->assertArrayNotHasKey('rows', $saved[0]['props']);
            $this->assertCount(3, $saved[0]['props']['left']);
            $this->assertCount(3, $saved[0]['props']['right']);
        } finally {
            $template?->delete();
        }
    }

    public function test_store_then_update_then_cleanup(): void
    {
        $blocks = json_encode(\App\Support\RaporFormat::defaultBlocks(), JSON_UNESCAPED_UNICODE);
        $name = 'Tes Desain ' . uniqid();

        $this->actingAs($this->user)
            ->post(route('wakasek.rapor-design.store'), ['name' => $name, 'blocks' => $blocks])
            ->assertRedirect();

        $template = RaporTemplate::where('school_id', $this->user->school_id)
            ->where('name', $name)
            ->first();

        try {
            $this->assertNotNull($template);

            $this->actingAs($this->user)
                ->post(route('wakasek.rapor-design.update', $template->id), [
                    'name' => $name . ' (v2)',
                    'blocks' => $blocks,
                ])
                ->assertRedirect();

            $template->refresh();
            $this->assertSame($name . ' (v2)', $template->name);
        } finally {
            if ($template) {
                $template->delete();
            }
        }
    }
}