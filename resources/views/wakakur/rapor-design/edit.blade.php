@extends('layouts.app')

@section('title', $template ? 'Edit Desain Rapor' : 'Desain Rapor Baru')

@section('content')
@php
    $isEdit = (bool) $template;
    $saveUrl = $isEdit
        ? route('wakasek.rapor-design.update', $template->id)
        : route('wakasek.rapor-design.store');
    $editorUrl = route('wakasek.rapor-design.editor');
    $previewUrl = route('wakasek.rapor-design.preview');
    $typeLabels = \App\Support\RaporFormat::labels();
@endphp

<style>
    @media print {
        .block-bar, .block-props, .add-block-bar, .cell-add { display: none !important; }
        .paper-editor { max-width: 100%; }
    }

    .paper-editor { max-width: 840px; margin: 0 auto; }
    .block-shell { margin-bottom: 14px; border: 1px dashed #cbd5e1; border-radius: 10px;
        background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.05); overflow: hidden; }
    .block-shell:hover { border-color: #818cf8; }
    .block-bar { display: flex; align-items: center; justify-content: space-between;
        padding: 6px 10px; background: #f8fafc; border-bottom: 1px dashed #e2e8f0; }
    .block-bar-label { font-size: 11px; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #94a3b8; }
    .block-bar-btns { display: flex; gap: 2px; }
    .block-bar-btns button { padding: 4px; border-radius: 6px; color: #94a3b8; cursor: pointer; }
    .block-bar-btns button:hover { color: #6366f1; background: #eef2ff; }
    .block-bar-btns button:last-child:hover { color: #ef4444; background: #fef2f2; }
    .block-render { padding: 18px 20px 14px; overflow-x: auto; }
    .block-props { border-top: 1px dashed #e2e8f0; }
    .block-props > div { border: 0; border-radius: 0; }
    .block-addon { margin: 10px 0 2px; text-align: left; }
    .addon-btn { display: inline-flex; align-items: center; gap: 2px; font-size: 11px;
        font-weight: 600; color: #6366f1; background: #eef2ff; border: 1px solid #c7d2fe;
        border-radius: 999px; padding: 2px 10px; cursor: pointer; }
    .addon-btn:hover { background: #e0e7ff; }
    .cell-add { vertical-align: middle; text-align: center; background: rgba(99,102,241,.05); }
    .cell-add .addon-btn { border-color: transparent; background: transparent; padding: 2px 6px; }
    .add-block-bar { display: flex; align-items: center; justify-content: center; gap: 10px;
        margin-top: 18px; padding: 12px; border: 1px dashed #cbd5e1; border-radius: 10px; }

    .kop-logo-editor { position: relative; width: 86px; height: 86px; flex-shrink: 0; cursor: pointer; }
    .kop-logo-editor img.kop-logo { width: 100%; height: 100%; object-fit: contain; }
    .kop-logo-overlay { position: absolute; inset: 0; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 2px; border-radius: 12px;
        background: rgba(99,102,241,.12); border: 1.5px dashed #818cf8; color: #4338ca;
        font-size: 11px; font-weight: 700; opacity: 0; transition: opacity .15s; cursor: pointer; }
    .kop-logo-overlay small { font-weight: 500; font-size: 9px; color: #6366f1; }
    .kop-logo-editor:hover .kop-logo-overlay { opacity: 1; }
    .kop-logo-editor.dragover .kop-logo-overlay { opacity: 1; background: rgba(99,102,241,.25); }
    .kop-logo-remove { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px;
        border-radius: 999px; background: #ef4444; color: #fff; font-size: 13px; line-height: 1;
        border: 0; cursor: pointer; box-shadow: 0 1px 4px rgba(0,0,0,.3); }
    .kop-title-line { line-height: 1.35; }
    .kop-title-text { display: inline-block; min-width: 8px; }
    .kop-title-text:empty::before { content: 'Judul kop…'; color: #94a3b8;
        font-weight: 400; font-style: italic; font-size: .8em; }
    .kop-title-text:focus { outline: 1px dashed #818cf8; border-radius: 2px; }
    .kop-title-ctls { display: inline-flex; align-items: center; gap: 2px;
        margin-left: 6px; vertical-align: middle;
        font-size: 10px; font-weight: 600; }
    .kop-title-ctls button { border: 1px solid #cbd5e1; background: #fff; color: #64748b;
        border-radius: 5px; padding: 1px 5px; cursor: pointer; line-height: 1.3; }
    .kop-title-ctls button:hover { color: #6366f1; border-color: #c7d2fe; }
    .kop-title-ctls .kop-title-del { background: #fecaca; color: #dc2626; border: 1px solid #fca5a5; }
    .kop-title-size { display: inline-flex; align-items: center; gap: 2px;
        border-left: 1px solid #e2e8f0; margin-left: 2px; padding-left: 4px; }
    .kop-title-size b { font-weight: 600; color: #4f46e5; }
    .kop-fontctl { display: inline-flex; align-items: center; gap: 3px; }
    .kop-fontctl-label { font-size: 11px; color: #94a3b8; margin-right: 2px; }
    .judul-line { line-height: 1.5; }
    .tahun-text { display: inline-block; min-width: 8px; }
    .tahun-text:empty::before { content: 'Semester…'; color: #94a3b8; }
    .judul-text { display: inline-block; min-width: 8px; }
    .judul-text:empty::before { content: 'Judul…'; color: #94a3b8;
        font-weight: 400; font-style: italic; font-size: .8em; }
    .judul-text:focus { outline: 1px dashed #818cf8; border-radius: 2px; }
    .judul .tahun { font-size: 12px; }
    .id-row .lbl { cursor: text; border-radius: 3px; }
    .id-row .lbl:focus { outline: 1px dashed #818cf8; }
    .id-row .lbl:empty::before { content: 'Label…'; color: #94a3b8; font-style: italic; }
    .id-row .id-ctls { flex: none; margin-left: 6px; }
    .id-row .id-ctls button { padding: 0 4px; }
    .id-row .id-handle { flex: none; cursor: grab; color: #94a3b8; margin-right: 5px; user-select: none; -webkit-user-select: none; touch-action: none; }
    .id-row .id-handle:hover { color: #6366f1; }
    .id-row .id-handle:active { cursor: grabbing; color: #4f46e5; }
    .id-row.id-drag-origin { opacity: .45; }
    .id-row.id-drop-before { outline: 2px dashed #818cf8; outline-offset: -1px; background: rgba(238,242,255,.6); }
    .id-col.id-drop-empty { outline: 2px dashed #818cf8; outline-offset: -2px; }
    .kop-title-line.kop-title-flash .kop-title-text {
        animation: kopFlash 1.2s ease-out;
    }
    @keyframes kopFlash {
        0% { background: rgba(99,102,241,.35); box-shadow: 0 0 0 3px rgba(99,102,241,.35); }
        100% { background: transparent; box-shadow: 0 0 0 0 transparent; }
    }
    .kop-title-add-row { display: flex; align-items: center; justify-content: center; gap: 12px;
        margin-top: 8px; }
    .kop-fontctl { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; }
    .kop-fontctl button { border: 1px solid #c7d2fe; background: #eef2ff; color: #6366f1;
        border-radius: 6px; padding: 1px 9px; font-size: 11px; font-weight: 700; cursor: pointer; }
    .kop-fontctl button:hover { background: #e0e7ff; }
</style>

@include('rapor.print-css')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <a href="{{ route('wakasek.rapor-design.index') }}" class="text-sm text-slate-500 dark:text-white/40 hover:text-primary inline-flex items-center gap-1">
            &larr; Kembali ke daftar
        </a>
        <h1 class="text-2xl font-bold tracking-tight mt-1">{{ $isEdit ? 'Edit Desain' : 'Desain Rapor Baru' }}</h1>
    </div>
    <button type="submit" form="rapor-designer" formaction="{{ $saveUrl }}"
        class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-primary hover:bg-primary text-white px-5 py-2.5 text-sm font-semibold transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Desain' }}
    </button>
</div>

<form id="rapor-designer" method="POST" action="{{ $editorUrl }}"
    data-preview-url="{{ $previewUrl }}" data-editor-url="{{ $editorUrl }}" data-logo-url="{{ route('wakasek.rapor-design.upload-logo') }}">
    @csrf

    {{-- ===== Toolbar: identitas desain + sampel preview ===== --}}
    <div class="mb-5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] p-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-white/40 mb-1">Nama Desain</label>
            <input type="text" name="name" value="{{ $nameInput }}" required maxlength="255"
                placeholder="mis. Rapor SMP Standar"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white placeholder:text-slate-400">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-white/40 mb-1">Berlaku Untuk Tahun Ajaran</label>
            <select name="academic_year_id"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                <option value="" {{ $ayInput ? '' : 'selected' }}>Semua tahun ajaran</option>
                @foreach ($academicYears as $ay)
                <option value="{{ $ay->id }}" @selected((string) $ayInput === (string) $ay->id)>{{ $ay->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-white/40 mb-1">Siswa Contoh</label>
            <select id="preview-student" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                @foreach ($students as $stu)
                <option value="{{ $stu->id }}" @if ($realtime['student']->id ?? null) @selected($realtime['student']->id === $stu->id) @endif>
                    {{ $stu->name }}@if ($stu->classRoom?->class_name) ({{ $stu->classRoom->class_name }}) @endif
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-white/40 mb-1">Semester Preview</label>
            <select id="preview-semester" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
            </select>
        </div>
    </div>

    {{-- ===== Paper (editor + preview terpadu) ===== --}}
    <div class="paper-editor mx-auto" id="paper-editor">
        @include('wakakur.rapor-design._paper', ['blocks' => $blocks, 'gradeTypes' => $gradeTypes, 'ctx' => $realtime])
    </div>

    <p class="mt-3 text-center text-[11px] text-slate-400 dark:text-white/30">
        Klik <b>⚙</b> pada blok untuk mengatur propertinya. Tambah kolom/baris langsung dari preview.
        Saat wali kelas mencetak, data asli siswa dipakai — angka dan identitas diambil otomatis.
    </p>

    {{-- Hidden state untuk aksi editor --}}
    <input type="hidden" id="blocks-json" name="blocks" value="{{ json_encode($blocks, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}">
    @if ($isEdit)
    <input type="hidden" name="template_id" value="{{ $template->id }}">
    @endif
    <input type="hidden" id="fld-action" name="action" value="">
    <input type="hidden" id="fld-target" name="target_id" value="">
    <input type="hidden" id="fld-dir" name="dir" value="">
    <input type="hidden" id="fld-path" name="path" value="">
    <input type="hidden" id="fld-idx" name="idx" value="">
    <input type="hidden" id="fld-addtype" name="add_type" value="">
    <input type="hidden" id="fld-step" name="step" value="">
    <input type="hidden" id="fld-set" name="set" value="">
    <input type="file" id="logo-file" accept="image/*" hidden>
</form>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('rapor-designer');
    var blocksJson = document.getElementById('blocks-json');
    var paperEditor = document.getElementById('paper-editor');
    var logoFile = document.getElementById('logo-file');
    var logoUploadUrl = form.dataset.logoUrl;
    var logoTarget = null;
    var renderVer = 0;
    var timer = null;

    function setPath(root, path, val) {
        if (path.indexOf('props.') !== 0) {
            root[path] = val;
            return;
        }
        var parts = path.split('.');
        var cur = root.props;
        for (var i = 1; i < parts.length; i++) {
            var p = parts[i];
            if (i === parts.length - 1) {
                cur[p] = val;
                return;
            }
            if (cur[p] === undefined) {
                cur[p] = /^\d+$/.test(parts[i + 1]) ? [] : {};
            }
            cur = cur[p];
        }
    }

    function collectBlocks() {
        var groups = {};
        var els = form.querySelectorAll('[data-b]');
        els.forEach(function (el) {
            var idx = parseInt(el.dataset.b, 10);
            if (isNaN(idx)) { return; }
            if (!groups[idx]) { groups[idx] = {}; }
            if (!groups[idx][el.dataset.path]) { groups[idx][el.dataset.path] = []; }
            groups[idx][el.dataset.path].push(el);
        });

        var blocks = Object.keys(groups).map(function (idx) {
            var b = { id: '', type: '', props: {} };
            Object.keys(groups[idx]).forEach(function (path) {
                var arr = groups[idx][path];
                var multi = arr.length > 1;
                var values = [];
                arr.forEach(function (el) {
                    var val;
                    if (el.type === 'checkbox') {
                        val = el.checked
                            ? ((el.hasAttribute('value') && el.value !== 'on') ? el.value : true)
                            : false;
                    } else {
                        val = el.value;
                    }
                    values.push(val);
                });
                if (values.length === 0) { return; }
                setPath(b, path, multi ? values : values[0]);
            });
            return b;
        }).filter(function (b) { return b.type !== ''; });

        blocksJson.value = JSON.stringify(blocks);
        return blocks;
    }

    form.addEventListener('input', function (e) {
        var el = e.target;
        if (el && el.isContentEditable && el.hasAttribute) {
            if (el.hasAttribute('data-edit-title')) {
                syncTitleLine(el);
            } else if (el.hasAttribute('data-edit-label')) {
                syncLabelLine(el);
            } else {
                return;
            }
            collectBlocks();
            return;
        }
        if (!el.hasAttribute('data-b')) { return; }
        collectBlocks();
        schedulePreview();
    });
    form.addEventListener('change', function (e) {
        if (!e.target.hasAttribute('data-b')) { return; }
        collectBlocks();
        schedulePreview();
    });
    form.addEventListener('focusout', function (e) {
        var el = e.target;
        if (el && el.isContentEditable && el.hasAttribute) {
            if (el.hasAttribute('data-edit-title')) {
                syncTitleLine(el);
            } else if (el.hasAttribute('data-edit-label')) {
                syncLabelLine(el);
            } else {
                return;
            }
            collectBlocks();
            schedulePreview();
        }
    });

    /* Preview live (update per-blok, panel properti tidak tersentuh) */
    function schedulePreview() {
        if (drag) { return; }
        clearTimeout(timer);
        timer = setTimeout(runPreview, 350);
    }

    function runPreview() {
        var ver = ++renderVer;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var payload = new FormData();
        payload.append('blocks', blocksJson.value);
        payload.append('semester', document.getElementById('preview-semester').value);
        payload.append('sample_student_id', document.getElementById('preview-student').value);

        fetch(form.dataset.previewUrl, {
            method: 'POST',
            headers: csrf ? { 'X-CSRF-TOKEN': csrf.content, 'Accept': 'application/json' } : { 'Accept': 'application/json' },
            body: payload
        }).then(jsonOrThrow)
          .then(function (data) {
              if (ver !== renderVer) { return; }
              if (!data || !data.slots) { return; }
              Object.keys(data.slots).forEach(function (idx) {
                  var slot = form.querySelector('[data-render-slot="' + idx + '"]');
                  if (slot) { slot.innerHTML = data.slots[idx]; }
              });
          }).catch(function () {});
    }

    /* Terapkan hasil aksi struktural: ganti seluruh paper + blocks JSON */
    function applyPaper(data) {
        if (!data || data.paper === undefined) { return; }
        renderVer++;
        paperEditor.innerHTML = data.paper;
        blocksJson.value = JSON.stringify(data.blocks || []);
    }

    /* Aksi editor via fetch (JSON) — tanpa reload halaman */
    function postEditor(btn) {
        clearTimeout(timer);
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var set = function (id, v) {
            var el = document.getElementById(id);
            if (el) { el.value = v === undefined || v === null ? '' : String(v); }
        };
        set('fld-action', btn.dataset.act);
        set('fld-target', btn.dataset.target);
        set('fld-dir', btn.dataset.dir);
        set('fld-path', btn.dataset.path);
        set('fld-idx', btn.dataset.idx);
        set('fld-step', btn.dataset.step);
        set('fld-set', btn.dataset.set);
        if (btn.dataset.act === 'add') {
            var addType = btn.dataset.addtype || document.getElementById('add-type').value;
            set('fld-addtype', addType);
        }

        var payload = new FormData();
        var fdKeys = {
            'fld-action': 'action',
            'fld-target': 'target_id',
            'fld-dir': 'dir',
            'fld-path': 'path',
            'fld-idx': 'idx',
            'fld-addtype': 'add_type',
            'fld-step': 'step',
            'fld-set': 'set'
        };
        Object.keys(fdKeys).forEach(function (id) {
            payload.append(fdKeys[id], document.getElementById(id).value);
        });
        payload.append('blocks', blocksJson.value);

        fetch(form.dataset.editorUrl, {
            method: 'POST',
            headers: csrf ? { 'X-CSRF-TOKEN': csrf.content, 'Accept': 'application/json' } : { 'Accept': 'application/json' },
            body: payload
        }).then(jsonOrThrow)
          .then(function (data) {
              applyPaper(data);
              if (btn.dataset.act === 'title-add') {
                  focusTitleLine(btn.dataset.target);
              }
          }).catch(function (err) {
              showNotice('Aksi editor gagal (' + (err && err.message ? err.message : 'koneksi') + '). Coba refresh halaman, lalu ulangi.');
          });
    }

    function focusTitleLine(targetId) {
        var shell = paperEditor.querySelector('.block-shell[data-block-id="' + targetId + '"]');
        var spans = shell ? shell.querySelectorAll('.kop-title-text[contenteditable]') : [];
        var span = spans[spans.length - 1];
        if (!span) { return; }
        var line = span.closest('.kop-title-line');
        if (line) {
            line.classList.remove('kop-title-flash');
            void line.offsetWidth;
            line.classList.add('kop-title-flash');
        }
        span.focus();
        var range = document.createRange();
        range.selectNodeContents(span);
        range.collapse(false);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }

    /* Papan peringatan kecil bila ada kesalahan server/network */
    function showNotice(msg) {
        var el = document.getElementById('editor-notice');
        if (!el) {
            el = document.createElement('div');
            el.id = 'editor-notice';
            el.style.cssText = 'position:fixed;top:12px;right:12px;z-index:9999;' +
                'max-width:300px;padding:8px 12px;border-radius:8px;font-size:13px;' +
                'background:#dc2626;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.2);';
            document.body.appendChild(el);
        }
        el.textContent = msg;
        el.style.display = 'block';
        clearTimeout(showNotice._t);
        showNotice._t = setTimeout(function () { el.style.display = 'none'; }, 5000);
    }

    function jsonOrThrow(r) {
        if (!r.ok) { throw new Error('HTTP ' + r.status); }
        return r.json();
    }

    function toggleProps(btn) {
        var shell = form.querySelector('.block-shell[data-block-id="' + btn.dataset.target + '"]');
        var panel = shell && shell.querySelector('[data-props-panel]');
        if (!panel) { return; }
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }

    form.addEventListener('click', function (e) {
        var remove = e.target.closest('[data-logo-remove]');
        if (remove) {
            setLogo(remove.dataset.logoRemove, '');
            return;
        }
        var drop = e.target.closest('[data-logo-drop]');
        if (drop) {
            logoTarget = drop.dataset.logoDrop;
            logoFile.click();
            return;
        }
        var btn = e.target.closest('[data-act]');
        if (!btn) { return; }
        if (btn.dataset.act === 'properties') {
            toggleProps(btn);
            return;
        }
        if (btn.dataset.act === 'title-size') {
            materializeVirtualLines(btn.closest('.block-shell'));
        }
        collectBlocks();
        postEditor(btn);
    });

    /* ===== Baris judul/kop inline (contenteditable) ===== */
    function titleBlockIndex(shell) {
        var probe = shell && (shell.querySelector('input[data-path="id"]') || shell.querySelector('input[data-path="props.logo"]'));
        return probe ? probe.dataset.b : null;
    }
    function materializeVirtualLines(shell) {
        var bIdx = titleBlockIndex(shell);
        if (bIdx === null) { return; }
        var linesField = shell.dataset.titleLines || 'title_lines';
        var sizesField = shell.dataset.titleSizes || 'title_sizes';
        shell.querySelectorAll('.kop-title-line[data-virtual]').forEach(function (container) {
            var txt = container.querySelector('.kop-title-text');
            var idx = container.dataset.lineIndex || '0';
            var size = container.dataset.size || '';
            var make = function (path, value) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.dataset.b = bIdx;
                inp.dataset.path = 'props.' + path + '.' + idx;
                inp.value = value;
                return inp;
            };
            container.appendChild(make(linesField, txt ? txt.textContent : ''));
            container.appendChild(make(sizesField, size));
            if (txt) { txt.removeAttribute('data-virtual'); }
            container.removeAttribute('data-virtual');
        });
    }
    function syncTitleLine(span) {
        var shell = span.closest('.block-shell');
        var container = span.closest('.kop-title-line');
        if (!shell || !container) { return; }
        var text = span.textContent;
        var key = span.dataset.editKey || '';
        var inp = key
            ? shell.querySelector('input[data-path="props.' + key + '"]')
            : container.querySelector('input[data-b]');
        if (!inp) {
            if (!text.trim() && !container.hasAttribute('data-virtual')) { return; }
            materializeVirtualLines(shell);
            inp = key
                ? shell.querySelector('input[data-path="props.' + key + '"]')
                : container.querySelector('input[data-b]');
        }
        if (inp) { inp.value = text; }
    }
    function syncLabelLine(span) {
        var shell = span.closest('.block-shell');
        if (!shell) { return; }
        var path = span.dataset.labelPath || '';
        if (!path) { return; }
        var inp = shell.querySelector('input[data-path="' + path + '"]');
        if (inp) { inp.value = span.textContent; }
    }

    /* ===== Drag & drop baris identitas (pointer events) ===== */
    var drag = null;
    var dragUi = null;

    function idRowIndex(row) {
        var col = row.parentElement;
        return Array.prototype.filter.call(col.children, function (el) {
            return el.classList.contains('id-row');
        }).indexOf(row);
    }

    function idDropTarget(px, py, excludeRow) {
        var el = document.elementFromPoint(px, py);
        if (!el || !el.closest) { return null; }
        if (!el.closest('.block-shell')) { return null; }
        var col = el.closest('.id-col');
        if (!col) { return null; }
        var path = col.getAttribute('id') === 'id-right' ? 'right' : 'left';
        var rows = Array.prototype.filter.call(col.children, function (el2) {
            return el2.classList.contains('id-row');
        });
        var idx = rows.length;
        for (var i = 0; i < rows.length; i++) {
            if (rows[i] === excludeRow) { continue; }
            var rc = rows[i].getBoundingClientRect();
            if (py < rc.top + rc.height / 2) { idx = i; break; }
        }
        return { path: path, idx: idx, col: col };
    }

    function clearDragUi() {
        if (dragUi && dragUi.col) { dragUi.col.classList.remove('id-drop-empty'); }
        if (dragUi && dragUi.row) { dragUi.row.classList.remove('id-drop-before'); }
        dragUi = null;
        if (drag && drag.row) { drag.row.classList.remove('id-drag-origin'); }
    }

    function updateDragUi(t) {
        if (dragUi && dragUi !== t) {
            if (dragUi.col) { dragUi.col.classList.remove('id-drop-empty'); }
            if (dragUi.row) { dragUi.row.classList.remove('id-drop-before'); }
        }
        dragUi = t;
        if (!t) { return; }
        var colRows = Array.prototype.filter.call(t.col.children, function (el) {
            return el.classList.contains('id-row');
        });
        var targetRow = colRows[t.idx];
        if (targetRow && targetRow !== drag.row) {
            targetRow.classList.add('id-drop-before');
            t.col.classList.remove('id-drop-empty');
        } else {
            t.col.classList.add('id-drop-empty');
        }
    }

    function commitDrag(target, d) {
        var targetIdx = target.idx;
        if (target.path === d.path && targetIdx > d.idx) { targetIdx--; }
        if (target.path === d.path && targetIdx === d.idx) { return false; }

        var blocks = JSON.parse(blocksJson.value);
        var bi = -1;
        for (var i = 0; i < blocks.length; i++) {
            if (blocks[i].id === d.id) { bi = i; break; }
        }
        if (bi < 0) { return false; }
        var props = blocks[bi].props || {};
        var src = (Array.isArray(props[d.path]) ? props[d.path] : []).slice();
        var item = src.splice(d.idx, 1)[0];
        if (item === undefined) { return false; }
        if (target.path === d.path) {
            src.splice(targetIdx, 0, item);
            props[d.path] = src;
        } else {
            var dst = (Array.isArray(props[target.path]) ? props[target.path] : []).slice();
            dst.splice(target.idx, 0, item);
            props[d.path] = src;
            props[target.path] = dst;
        }
        blocksJson.value = JSON.stringify(blocks);
        return true;
    }

    function submitDragReorder() {
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var payload = new FormData();
        payload.append('action', 'reorder');
        payload.append('target_id', '');
        payload.append('dir', '');
        payload.append('path', '');
        payload.append('idx', '');
        payload.append('blocks', blocksJson.value);
        fetch(form.dataset.editorUrl, {
            method: 'POST',
            headers: csrf ? { 'X-CSRF-TOKEN': csrf.content, 'Accept': 'application/json' } : { 'Accept': 'application/json' },
            body: payload
        }).then(jsonOrThrow)
          .then(function (data) { applyPaper(data); })
          .catch(function () {});
    }

    form.addEventListener('pointerdown', function (e) {
        if (e.button !== 0 && e.pointerType === 'mouse') { return; }
        if (!e.target || !e.target.closest) { return; }
        var h = e.target.closest('[data-drag-handle]');
        if (!h) { return; }
        var row = h.closest('.id-row');
        var col = h.closest('.id-col');
        if (!row || !col) { return; }
        renderVer++;
        drag = {
            id: col.closest('.id-table').getAttribute('data-block-target'),
            row: row,
            path: col.getAttribute('id') === 'id-right' ? 'right' : 'left',
            idx: idRowIndex(row),
            startX: e.clientX,
            startY: e.clientY,
            moved: false
        };
        try { h.setPointerCapture(e.pointerId); } catch (err) {}
        e.preventDefault();
    }, true);

    window.addEventListener('pointermove', function (e) {
        if (!drag) { return; }
        if (!drag.moved) {
            if (Math.abs(e.clientX - drag.startX) + Math.abs(e.clientY - drag.startY) < 6) { return; }
            drag.moved = true;
            drag.row.classList.add('id-drag-origin');
        }
        updateDragUi(idDropTarget(e.clientX, e.clientY, drag.row));
    });

    window.addEventListener('pointerup', function (e) {
        if (!drag) { return; }
        var d = drag;
        var target = d.moved ? idDropTarget(e.clientX, e.clientY, d.row) : null;
        clearDragUi();
        drag = null;
        if (target && commitDrag(target, d)) {
            submitDragReorder();
        } else {
            schedulePreview();
        }
    });

    window.addEventListener('pointercancel', function () {
        if (!drag) { return; }
        clearDragUi();
        drag = null;
        schedulePreview();
    });

    /* ===== Logo kop: drag & drop / klik-pilih, live preview ===== */
    function uploadLogo(targetId, file) {
        if (!file) { return; }
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var fd = new FormData();
        fd.append('file', file);
        fetch(logoUploadUrl, {
            method: 'POST',
            headers: csrf ? { 'X-CSRF-TOKEN': csrf.content, 'Accept': 'application/json' } : { 'Accept': 'application/json' },
            body: fd
        }).then(jsonOrThrow)
          .then(function (data) {
              if (data && data.path) { setLogo(targetId, data.path); }
          }).catch(function (err) {
              showNotice('Upload logo gagal (' + (err && err.message ? err.message : 'ukuran/format') + '). Pastikan berkas jpg/png/webp maks 2MB.');
          });
    }

    function setLogo(targetId, path) {
        var shell = form.querySelector('.block-shell[data-block-id="' + targetId + '"]');
        var inp = shell && shell.querySelector('input[data-path="props.logo"]');
        if (inp) { inp.value = path || ''; }
        collectBlocks();
        runPreview();
    }

    form.addEventListener('dragover', function (e) {
        var drop = e.target.closest('[data-logo-drop]');
        if (!drop) { return; }
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        drop.classList.add('dragover');
    });
    form.addEventListener('dragleave', function (e) {
        var drop = e.target.closest('[data-logo-drop]');
        if (drop) { drop.classList.remove('dragover'); }
    });
    form.addEventListener('drop', function (e) {
        if (!e.target.closest('[data-logo-drop]')) { return; }
        e.preventDefault();
        var drop = e.target.closest('[data-logo-drop]');
        drop.classList.remove('dragover');
        var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
        if (f) { uploadLogo(drop.dataset.logoDrop, f); }
    });
    logoFile.addEventListener('change', function () {
        if (logoTarget && logoFile.files[0]) {
            uploadLogo(logoTarget, logoFile.files[0]);
        }
        logoFile.value = '';
    });

    ['preview-semester', 'preview-student'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                collectBlocks();
                runPreview();
            });
        }
    });
})();
</script>
@endpush