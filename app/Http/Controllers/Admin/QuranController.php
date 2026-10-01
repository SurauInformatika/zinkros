<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuranMaster;
use Illuminate\View\View;

class QuranController extends Controller
{
    public function index(): View
    {
        $surahs = QuranMaster::orderBy('surah_number')->get();

        return view('admin.quran.index', compact('surahs'));
    }
}
