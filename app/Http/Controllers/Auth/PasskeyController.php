<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PasskeyController extends Controller
{
    /**
     * Show the passkey management page for the authenticated user.
     */
    public function index(): View
    {
        $keys = auth()->user()->webauthnKeys()->orderByDesc('created_at')->get();

        return view('auth.passkeys', compact('keys'));
    }
}
