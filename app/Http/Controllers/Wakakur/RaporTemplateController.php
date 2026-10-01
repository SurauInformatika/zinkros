<?php

namespace App\Http\Controllers\Wakakur;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\RaporTemplate;
use App\Services\RaporDataService;
use App\Support\RaporFormat;
use Illuminate\Http\Request;

class RaporTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $templates = RaporTemplate::with(['academicYear', 'creator'])
            ->where('school_id', $user->school_id)
            ->orderBy('is_active', 'desc')
            ->orderByDesc('updated_at')
            ->get();

        $academicYears = AcademicYear::orderBy('start_date')->get();

        return view('wakakur.rapor-design.index', compact('templates', 'academicYears'));
    }

    public function create()
    {
        return $this->renderEditor(null, RaporFormat::defaultBlocks());
    }

    public function edit(RaporTemplate $template)
    {
        $this->guardSchool($template);

        return $this->renderEditor($template, RaporFormat::normalize($template->blocks));
    }

    /**
     * Aksi editor bersama (create & edit): tambah/pindah/hapus blok,
     * lalu re-render editor. Save dilakukan lewat store/update.
     */
    public function editorAction(Request $request)
    {
        $templateId = $request->input('template_id');
        $template = null;
        if ($templateId) {
            $template = RaporTemplate::where('id', $templateId)
                ->where('school_id', auth()->user()->school_id)
                ->first();
        }

        $blocks = $this->blocksFromRequest($request);
        $action = $request->input('action');

        if ($action === 'add') {
            $type = (string) $request->input('add_type', 'teks-bebas');
            $blocks[] = RaporFormat::newBlock($type);
        } elseif ($action === 'move') {
            $targetId = $request->input('target_id');
            $dir = $request->input('dir', 'up');
            $indexes = collect($blocks)->pluck('id')->all();
            $index = array_search($targetId, $indexes, true);
            if ($index !== false) {
                $swap = $dir === 'up' ? $index - 1 : $index + 1;
                if ($swap >= 0 && $swap < count($blocks)) {
                    [$blocks[$index], $blocks[$swap]] = [$blocks[$swap], $blocks[$index]];
                }
            }
        } elseif ($action === 'delete') {
            $targetId = $request->input('target_id');
            $blocks = collect($blocks)->filter(fn ($b) => $b['id'] ?? null !== $targetId)->values()->all();
        } elseif ($action === 'list-add') {
            $blocks = $this->addListItem($blocks, $request->input('target_id'), (string) $request->input('path'));
        } elseif ($action === 'list-del') {
            $blocks = $this->deleteListItem(
                $blocks,
                $request->input('target_id'),
                (string) $request->input('path'),
                (int) $request->input('idx', -1)
            );
        } elseif ($action === 'prop-inc') {
            $blocks = $this->incrementListItem(
                $blocks,
                $request->input('target_id'),
                (string) $request->input('path'),
                (int) $request->input('step', 1)
            );
        } elseif ($action === 'set') {
            $blocks = $this->setListItem(
                $blocks,
                $request->input('target_id'),
                (string) $request->input('path'),
                $request->input('set', 1)
            );
        } elseif ($action === 'title-add') {
            $blocks = $this->titleLineAdd($blocks, $request->input('target_id'));
        } elseif ($action === 'title-del') {
            $blocks = $this->titleLineDelete($blocks, $request->input('target_id'), (int) $request->input('idx', -1));
        } elseif ($action === 'title-move') {
            $blocks = $this->titleLineMove(
                $blocks,
                $request->input('target_id'),
                (string) $request->input('dir', 'up'),
                (int) $request->input('idx', 0)
            );
        } elseif ($action === 'title-size') {
            $blocks = $this->titleLineChangeSize(
                $blocks,
                $request->input('target_id'),
                (int) $request->input('idx', 0),
                (string) $request->input('dir', 'up')
            );
        } elseif ($action === 'col-move') {
            $blocks = $this->moveBetweenColumns(
                $blocks,
                $request->input('target_id'),
                (string) $request->input('path'),
                (int) $request->input('idx', -1)
            );
        } elseif ($action === 'list-move') {
            $blocks = $this->listMove(
                $blocks,
                $request->input('target_id'),
                (string) $request->input('path'),
                (int) $request->input('idx', -1),
                (string) $request->input('dir', 'up')
            );
        } elseif ($action === 'reorder') {
            $blocks = $this->blocksFromRequest($request);
        }

        if ($template) {
            $template->blocks = $blocks;
        }

        $paper = $this->paperEditorHtml($blocks);

        return response()->json([
            'paper' => $paper,
            'blocks' => $blocks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
        ]);

        $blocks = $this->blocksFromRequest($request);

        $user = auth()->user();
        $count = RaporTemplate::where('school_id', $user->school_id)->count();

        $template = RaporTemplate::create([
            'school_id' => $user->school_id,
            'academic_year_id' => $request->input('academic_year_id') ?: null,
            'name' => $validated['name'],
            'blocks' => $blocks,
            'is_active' => $count === 0,
            'created_by' => $user->id,
        ]);

        return redirect()->route('wakasek.rapor-design.edit', $template->id)
            ->with('success', 'Desain rapor berhasil disimpan.');
    }

    public function update(Request $request, RaporTemplate $template)
    {
        $this->guardSchool($template);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
        ]);

        $template->update([
            'name' => $validated['name'],
            'academic_year_id' => $request->input('academic_year_id') ?: null,
            'blocks' => $this->blocksFromRequest($request),
        ]);

        return redirect()->route('wakasek.rapor-design.edit', $template->id)
            ->with('success', 'Desain rapor berhasil diperbarui.');
    }

    public function destroy(RaporTemplate $template)
    {
        $this->guardSchool($template);
        $wasActive = $template->is_active;
        $template->delete();

        if ($wasActive) {
            $fallback = RaporTemplate::where('school_id', auth()->user()->school_id)
                ->orderByDesc('updated_at')
                ->first();
            if ($fallback) {
                $fallback->update(['is_active' => true]);
            }
        }

        return redirect()->route('wakasek.rapor-design.index')
            ->with('success', 'Desain rapor dihapus.');
    }

    public function duplicate(RaporTemplate $template)
    {
        $this->guardSchool($template);

        RaporTemplate::create([
            'school_id' => $template->school_id,
            'academic_year_id' => $template->academic_year_id,
            'name' => $template->name.' (Salinan)',
            'blocks' => $template->blocks,
            'is_active' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('wakasek.rapor-design.index')
            ->with('success', 'Salinan desain rapor dibuat.');
    }

    public function activate(RaporTemplate $template)
    {
        $this->guardSchool($template);

        RaporTemplate::where('school_id', $template->school_id)
            ->update(['is_active' => false]);
        $template->update(['is_active' => true]);

        return redirect()->route('wakasek.rapor-design.index')
            ->with('success', "Desain rapor \"{$template->name}\" diaktifkan untuk cetak.");
    }

    /**
     * Preview live dari editor: mengembalikan HTML per blok (slot) supaya
     * panel properti yang sedang terbuka tidak ikut ter-refresh.
     */
    public function preview(Request $request)
    {
        $blocks = $this->blocksFromRequest($request);
        $semester = (int) $request->input('semester', 1) === 2 ? 2 : 1;
        $sampleId = $request->input('sample_student_id') ?: null;

        $ctx = app(RaporDataService::class)->sampleFor(auth()->user(), $semester, $sampleId);

        $slots = [];
        foreach ($blocks as $i => $block) {
            if (($block['type'] ?? '') !== '') {
                $slots[$i] = view('wakakur.rapor-design._block-render', [
                    'block' => $block,
                    'i' => $i,
                    'ctx' => $ctx,
                ])->render();
            }
        }

        return response()->json(['slots' => $slots]);
    }

    protected function addListItem(array $blocks, $targetId, string $path): array
    {
        $defaults = [
            'rows' => ['label' => 'Baris Baru', 'key' => 'none'],
            'left' => ['label' => 'Baris Baru', 'key' => 'none'],
            'right' => ['label' => 'Baris Baru', 'key' => 'none'],
            'custom_columns' => ['label' => 'Kolom'],
            'columns' => ['label' => 'Kolom'],
            'boxes' => ['label' => 'Tanda Tangan', 'key' => 'manual'],
        ];

        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) === $targetId) {
                $item = $defaults[$path] ?? ['label' => ''];
                $block['props'][$path] = $block['props'][$path] ?? [];
                $block['props'][$path][] = $item;
                break;
            }
        }

        return $blocks;
    }

    protected function deleteListItem(array $blocks, $targetId, string $path, int $index): array
    {
        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) === $targetId && isset($block['props'][$path]) && is_array($block['props'][$path])) {
                if ($index >= 0 && $index < count($block['props'][$path])) {
                    array_splice($block['props'][$path], $index, 1);
                }
                break;
            }
        }

        return $blocks;
    }

    protected function incrementListItem(array $blocks, $targetId, string $path, int $step): array
    {
        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) === $targetId) {
                $current = (int) ($block['props'][$path] ?? 0);
                $block['props'][$path] = max(0, $current + $step);
                break;
            }
        }

        return $blocks;
    }

    protected function setListItem(array $blocks, $targetId, string $path, $value): array
    {
        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) === $targetId) {
                $block['props'][$path] = is_numeric($value)
                    ? (int) $value
                    : (filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
                break;
            }
        }

        return $blocks;
    }

    protected function moveBetweenColumns(array $blocks, $targetId, string $path, int $index): array
    {
        if (! in_array($path, ['left', 'right'], true)) {
            return $blocks;
        }
        $dest = $path === 'left' ? 'right' : 'left';

        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) !== $targetId) {
                continue;
            }
            $source = $block['props'][$path] ?? [];
            if (! is_array($source) || $index < 0 || $index >= count($source)) {
                break;
            }
            $item = array_splice($source, $index, 1)[0];
            $block['props'][$path] = $source;
            $block['props'][$dest] = $block['props'][$dest] ?? [];
            $block['props'][$dest][] = $item;
            break;
        }

        return $blocks;
    }

    protected function listMove(array $blocks, $targetId, string $path, int $index, string $dir): array
    {
        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) !== $targetId) {
                continue;
            }
            $list = $block['props'][$path] ?? [];
            if (! is_array($list) || $index < 0 || $index >= count($list)) {
                break;
            }
            $swap = $dir === 'up' ? $index - 1 : $index + 1;
            if ($swap >= 0 && $swap < count($list)) {
                [$list[$index], $list[$swap]] = [$list[$swap], $list[$index]];
                $block['props'][$path] = array_values($list);
            }
            break;
        }

        return $blocks;
    }

    public function titleFieldsFor(array $block): array
    {
        $isJudul = ($block['type'] ?? null) === 'judul';

        return $isJudul ? ['lines', 'line_sizes'] : ['title_lines', 'title_sizes'];
    }

    protected function titleLineAdd(array $blocks, $targetId): array
    {
        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) === $targetId) {
                [$linesField, $sizesField] = $this->titleFieldsFor($block);
                $lines = $block['props'][$linesField] ?? [];
                if (! is_array($lines)) { $lines = []; }
                if (count($lines) === 0) {
                    if (($block['type'] ?? null) === 'judul') {
                        $seeds = [(string) ($block['props']['text'] ?? 'LAPORAN HASIL BELAJAR PESERTA DIDIK')];
                    } else {
                        $school = auth()->user()->school;
                        $seeds = [];
                        if ($school?->name) {
                            $seeds[] = (string) $school->name;
                        }
                        if (filled($school?->address)) {
                            $seeds[] = (string) $school->address;
                        }
                        if ($seeds === []) {
                            $seeds[] = (string) \App\Models\PlatformSetting::appName();
                        }
                    }
                    $lines = $seeds;
                }
                $lines[] = '';
                $block['props'][$linesField] = $lines;
                $this->syncTitleSizes($block, $linesField, $sizesField);
                break;
            }
        }

        return $blocks;
    }

    protected function titleLineDelete(array $blocks, $targetId, int $index): array
    {
        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) === $targetId) {
                [$linesField, $sizesField] = $this->titleFieldsFor($block);
                $lines = $block['props'][$linesField] ?? [];
                if (! is_array($lines)) {
                    break;
                }
                if ($index >= 0 && $index < count($lines)) {
                    array_splice($lines, $index, 1);
                    $block['props'][$linesField] = $lines;
                    if (isset($block['props'][$sizesField]) && is_array($block['props'][$sizesField])) {
                        array_splice($block['props'][$sizesField], $index, 1);
                    }
                    $this->syncTitleSizes($block, $linesField, $sizesField);
                }
                break;
            }
        }

        return $blocks;
    }

    protected function titleLineMove(array $blocks, $targetId, string $dir, int $index): array
    {
        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) === $targetId) {
                [$linesField, $sizesField] = $this->titleFieldsFor($block);
                $lines = $block['props'][$linesField] ?? [];
                if (! is_array($lines)) {
                    break;
                }
                $lines = array_values($lines);
                $swap = $dir === 'up' ? $index - 1 : $index + 1;
                if ($index >= 0 && $swap >= 0 && $swap < count($lines)) {
                    [$lines[$index], $lines[$swap]] = [$lines[$swap], $lines[$index]];
                    $block['props'][$linesField] = array_values($lines);
                    if (isset($block['props'][$sizesField]) && is_array($block['props'][$sizesField])) {
                        $sizes = array_values($block['props'][$sizesField]);
                        [$sizes[$index], $sizes[$swap]] = [$sizes[$swap], $sizes[$index]];
                        $block['props'][$sizesField] = $sizes;
                    }
                    $this->syncTitleSizes($block, $linesField, $sizesField);
                }
                break;
            }
        }

        return $blocks;
    }

    protected function titleLineChangeSize(array $blocks, $targetId, int $index, string $dir): array
    {
        foreach ($blocks as &$block) {
            if (($block['id'] ?? null) === $targetId) {
                [$linesField, $sizesField] = $this->titleFieldsFor($block);
                $lines = $block['props'][$linesField] ?? [];
                if (! is_array($lines) || $index < 0 || $index >= count($lines)) {
                    break;
                }
                $sizes = $block['props'][$sizesField] ?? [];
                if (! is_array($sizes)) { $sizes = []; }
                $fallback = $this->titleBaseSize($block);
                $current = isset($sizes[$index]) ? (int) $sizes[$index] : $fallback;
                $sizes[$index] = max(8, min(72, $current + ($dir === 'up' ? 1 : -1)));
                $block['props'][$sizesField] = array_values($sizes);
                $this->syncTitleSizes($block, $linesField, $sizesField);
                break;
            }
        }

        return $blocks;
    }

    protected function titleBaseSize(array $block): int
    {
        $isJudul = ($block['type'] ?? null) === 'judul';

        return (int) (($isJudul ? ($block['props']['font_size'] ?? 16) : ($block['props']['title_font_size'] ?? 15)) ?: ($isJudul ? 16 : 15));
    }

    protected function syncTitleSizes(array &$block, ?string $linesField = null, ?string $sizesField = null): void
    {
        if ($linesField === null) {
            [$linesField, $sizesField] = $this->titleFieldsFor($block);
        }
        $lines = $block['props'][$linesField] ?? [];
        $total = is_array($lines) ? count($lines) : 0;
        $fallback = $this->titleBaseSize($block);
        $sizes = $block['props'][$sizesField] ?? [];
        if (! is_array($sizes)) { $sizes = []; }
        $out = [];
        for ($i = 0; $i < $total; $i++) {
            $out[] = max(8, min(72, isset($sizes[$i]) ? (int) $sizes[$i] : $fallback));
        }
        $block['props'][$sizesField] = $out;
    }

    /**
     * Unggah logo kop sekolah (drag & drop live preview dari editor desain).
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'file.required' => 'Pilih berkas gambar.',
            'file.image' => 'Berkas harus berupa gambar.',
            'file.mimes' => 'Format harus jpeg, png, jpg, atau webp.',
            'file.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $file = $request->file('file');
        $name = 'rapor-kop-'.auth()->id().'-'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('rapor', $name, 'public');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/'.$path),
        ]);
    }

    protected function paperEditorHtml(array $blocks): string
    {
        $ctx = app(RaporDataService::class)->sampleFor(auth()->user(), 1);

        return view('wakakur.rapor-design._paper', [
            'blocks' => $blocks,
            'gradeTypes' => $this->gradeTypes(),
            'ctx' => $ctx,
        ])->render();
    }

    protected function gradeTypes()
    {
        return \App\Models\GradeType::where('school_id', auth()->user()->school_id)
            ->active()->ordered()->get();
    }

protected function guardSchool(RaporTemplate $template): void
    {
        if (auth()->user()->school_id !== $template->school_id) abort(403);
    }

    protected function renderEditor(?RaporTemplate $template, array $blocks, string $name = '', $ayId = null)
    {
        $user = auth()->user();
        $school = $user->school;
        $academicYears = AcademicYear::orderBy('start_date')->get();
        $gradeTypes = $this->gradeTypes();
        $students = \App\Models\Student::where('school_id', $user->school_id)
            ->orderBy('name')->get(['id', 'name', 'class_id']);
        $realtime = app(RaporDataService::class)->sampleFor($user, 1);

        $nameInput = $name !== '' ? $name : ($template?->name ?? '');
        $ayInput = $ayId ?? $template?->academic_year_id;

        return view('wakakur.rapor-design.edit', compact(
            'template', 'blocks', 'school', 'academicYears', 'gradeTypes',
            'students', 'realtime', 'nameInput', 'ayInput'
        ));
    }

    protected function blocksFromRequest(Request $request): array
    {
        $json = $request->input('blocks');

        if (is_array($json) && $json !== []) {
            return RaporFormat::normalize($json);
        }

        if (is_string($json) && trim($json) !== '') {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return RaporFormat::normalize($decoded);
            }
        }

        return RaporFormat::normalize((array) $request->input('block', []));
    }
}