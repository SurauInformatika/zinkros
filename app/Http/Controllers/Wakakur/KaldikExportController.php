<?php

namespace App\Http\Controllers\Wakakur;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Support\KaldikDocument;
use Barryvdh\DomPDF\Facade\Pdf;

class KaldikExportController extends Controller
{
    public function show(AcademicCalendar $kaldik)
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);

        $kaldik->load([
            'school',
            'academicYear',
            'template',
            'creator',
            'approver',
            'levelStructures',
            'holidays',
            'classOverrides',
        ]);

        $doc = new KaldikDocument($kaldik);

        $pdf = Pdf::loadView('kepsek.kaldik-pdf', ['kaldik' => $kaldik, 'doc' => $doc])
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', false);

        return $pdf->stream($doc->filename());
    }
}