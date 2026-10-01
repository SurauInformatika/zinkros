<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GuruTemplate;
use App\Exports\SiswaTemplate;
use App\Exports\StaffTemplate;
use App\Http\Controllers\Controller;
use App\Imports\GuruImport;
use App\Imports\SiswaImport;
use App\Imports\StaffImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ExcelController extends Controller
{
    // ── Template Downloads ──

    public function downloadGuruTemplate()
    {
        return Excel::download(new GuruTemplate, 'template_guru.xlsx');
    }

    public function downloadStaffTemplate()
    {
        return Excel::download(new StaffTemplate, 'template_staff.xlsx');
    }

    public function downloadSiswaTemplate()
    {
        return Excel::download(new SiswaTemplate, 'template_siswa.xlsx');
    }

    // ── Imports ──

    public function importGuru(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ]);

        try {
            $schoolId = auth()->user()->school_id;
            $import = new GuruImport($schoolId);
            Excel::import($import, $request->file('file'));

            $msg = "Berhasil import {$import->getImportedCount()} guru.";
            if ($import->getSkippedCount() > 0) {
                $msg .= " {$import->getSkippedCount()} dilewati (email sudah ada).";
            }

            return redirect()->route('admin.guru.index')->with('status', $msg);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = collect($failures)->map(fn($f) => 'Baris ' . $f->row() . ': ' . collect($f->errors())->implode(', '))->implode('; ');
            return redirect()->route('admin.guru.index')->with('error', 'Validasi gagal: ' . $errors);
        } catch (\Exception $e) {
            return redirect()->route('admin.guru.index')->with('error', 'Gagal import guru: ' . $e->getMessage());
        }
    }

    public function importStaff(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ]);

        try {
            $schoolId = auth()->user()->school_id;
            $import = new StaffImport($schoolId);
            Excel::import($import, $request->file('file'));

            $msg = "Berhasil import {$import->getImportedCount()} staff.";
            if ($import->getSkippedCount() > 0) {
                $msg .= " {$import->getSkippedCount()} dilewati (email sudah ada).";
            }

            return redirect()->route('admin.staff.index')->with('status', $msg);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = collect($failures)->map(fn($f) => 'Baris ' . $f->row() . ': ' . collect($f->errors())->implode(', '))->implode('; ');
            return redirect()->route('admin.staff.index')->with('error', 'Validasi gagal: ' . $errors);
        } catch (\Exception $e) {
            return redirect()->route('admin.staff.index')->with('error', 'Gagal import staff: ' . $e->getMessage());
        }
    }

    public function importSiswa(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ]);

        try {
            $schoolId = auth()->user()->school_id;
            $import = new SiswaImport($schoolId);
            Excel::import($import, $request->file('file'));

            $msg = "Berhasil import {$import->getImportedCount()} siswa.";
            if ($import->getSkippedCount() > 0) {
                $msg .= " {$import->getSkippedCount()} dilewati (NIS/NISN sudah ada).";
            }
            if ($import->getSkippedNoClassCount() > 0) {
                $msg .= " {$import->getSkippedNoClassCount()} dilewati (kelas tidak ditemukan).";
            }

            return redirect()->route('admin.siswa.index')->with('status', $msg);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = collect($failures)->map(fn($f) => 'Baris ' . $f->row() . ': ' . collect($f->errors())->implode(', '))->implode('; ');
            return redirect()->route('admin.siswa.index')->with('error', 'Validasi gagal: ' . $errors);
        } catch (\Exception $e) {
            return redirect()->route('admin.siswa.index')->with('error', 'Gagal import siswa: ' . $e->getMessage());
        }
    }
}
