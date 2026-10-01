<style>
    @page {
        size: A4;
        margin: 12mm 14mm 12mm 14mm;
    }
    * { box-sizing: border-box; }
    .rapor-paper-body {
        font-family: 'Times New Roman', Georgia, serif;
        color: #111;
        margin: 0;
        background: #e2e8f0;
    }
    .no-print {
        font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
    }
    .print-toolbar {
        max-width: 800px;
        margin: 20px auto 0;
        padding: 0 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .print-toolbar a,
    .print-toolbar button {
        border-radius: 10px;
        padding: 9px 16px;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .print-toolbar a.primary,
    .print-toolbar button.primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }
    .print-toolbar select,
    .print-toolbar a.select {
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
    }
    .paper {
        max-width: 800px;
        margin: 16px auto 48px;
        background: #fff;
        padding: 20mm 16mm;
        box-shadow: 0 4px 20px rgba(0,0,0,.08);
    }
    .paper .page-title {
        display: none;
    }
    /* Kop */
    .kop { border-bottom: 3px double #000; padding-bottom: 10px; }
    .kop-inner { display: flex; align-items: center; gap: 16px; }
    .kop-logo { width: 86px; height: 86px; flex-shrink: 0; object-fit: contain; }
    .kop-logo-placeholder {
        width: 86px; height: 86px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid #cbd5e1; border-radius: 12px;
        color: #94a3b8; font-size: 30px; font-weight: bold;
        background: #f8fafc;
    }
    .kop-text { flex: 1; text-align: center; }
    .kop-text .lembaga { font-size: 12px; letter-spacing: .5px; }
    .kop-text .nama { font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
    .kop-title-line { line-height: 1.3; }
    .kop-text .alamat {
        font-size: 11px; margin-top: 5px; line-height: 1.4;
    }
    .kop-text .kontak { font-size: 11px; }
    .judul {
        text-align: center;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        line-height: 1.5;
        margin: 14px 0 10px;
    }
    .judul .tahun { font-size: 12px; font-weight: 600; }
    /* Identitas */
    table.id { width: 100%; border-collapse: collapse; font-size: 12px; }
    table.id td { padding: 3px 2px; vertical-align: top; }
    table.id .lbl { width: 42%; min-width: 42%; }
    table.id .dotted {
        border-bottom: 1px dotted #64748b; display: inline-block; min-width: 60px;
    }
    table.id .id-col { width: 50%; vertical-align: top; }
    table.id .id-col + .id-col { padding-left: 14px; }
    table.id .id-row { display: flex; align-items: baseline; }
    table.id .id-row .lbl { width: 42%; min-width: 42%; flex: none; }
    table.id .id-row .id-val { flex: 1 1 auto; min-width: 0; }
    table.kv { width: 100%; border-collapse: collapse; font-size: 11.5px; }
    table.kv td { padding: 2px 4px; vertical-align: top; }
    table.kv td.lbl { width: 55%; }
    h3.section {
        font-size: 12px; font-weight: 700; text-transform: uppercase;
        margin: 16px 0 6px; border-bottom: 1px solid #000; padding-bottom: 3px;
    }
    table.cells { width: 100%; border-collapse: collapse; font-size: 11px; }
    table.cells th, table.cells td {
        border: 1px solid #000; padding: 4px 5px; text-align: center;
    }
    table.cells th { background: #f1f5f9; font-weight: 700; }
    table.cells td.left { text-align: left; }
    .keterangan { font-size: 10.5px; margin-top: 8px; line-height: 1.5; }
    .ttd { display: flex; justify-content: space-between; gap: 20px; margin-top: 20px; }
    .ttd .blok { flex: 1; text-align: center; font-size: 11.5px; }
    .ttd .space { height: 64px; }
    .catatan { font-size: 11px; margin-top: 8px; }
    .catatan .garis { border-bottom: 1px dotted #334155; display: block; margin: 4px 0; }
    .sempurna { text-align: center; font-size: 11px; margin-top: 16px; font-weight: 600; }
    @media print {
        .no-print { display: none !important; }
        .rapor-paper-body { background: #fff; }
        .paper { box-shadow: none; max-width: none; margin: 0; padding: 0; }
    }
</style>