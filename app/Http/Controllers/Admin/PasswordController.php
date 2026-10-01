<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(User $user): View
    {
        return view('admin.password.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $redirectMap = [
            'guru' => route('admin.guru.index'),
            'staff' => route('admin.staff.index'),
            'ortu' => route('admin.pengguna.index', ['role' => 'ortu']),
            'keuangan' => route('admin.pengguna.index', ['role' => 'keuangan']),
        ];

        $redirect = $redirectMap[$user->role] ?? route('admin.dashboard');

        return redirect($redirect)
            ->with('status', "Password {$user->name} berhasil di-reset.");
    }
}
