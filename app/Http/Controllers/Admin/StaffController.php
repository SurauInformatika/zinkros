<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staffs = User::query()
            ->where('role', User::ROLE_STAFF)
            ->orderBy('position')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.staff.index', compact('staffs'));
    }

    public function create(): View
    {
        return view('admin.staff.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'position' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['role'] = User::ROLE_STAFF;
        $validated['created_by'] = $request->user()->id;

        User::create($validated);

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Akun staf berhasil ditambahkan.');
    }

    public function edit(User $staff): View
    {
        abort_unless($staff->isStaff(), 404);

        return view('admin.staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        abort_unless($staff->isStaff(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($staff->id)],
            'position' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $request->filled('password')) {
            unset($validated['password']);
        }

        $staff->update($validated);

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Data staf berhasil diperbarui.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        abort_unless($staff->isStaff(), 404);

        $staff->delete();

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Akun staf berhasil dihapus.');
    }
}
