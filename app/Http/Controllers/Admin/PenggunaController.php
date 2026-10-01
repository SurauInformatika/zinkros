<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    /**
     * The tab key used for the single "Wakil Kepala Sekolah" group in the
     * Pengguna menu. It maps to the unified `wakasek` role in the DB; the
     * jabatan (position) is stored on the `position` column.
     */
    public const WAKASEK_GROUP = 'wakasek';

    private function enabledRoles(Request $request): array
    {
        return $request->user()->school?->penggunaRoles() ?? \App\Models\School::PENGGUNA_ROLES;
    }

    /**
     * Whether the given role key maps to the single wakasek tab.
     */
    private function isWakasekRole(?string $role): bool
    {
        return $role === self::WAKASEK_GROUP || $role === User::ROLE_WAKASEK;
    }

    public function index(Request $request): View|RedirectResponse
    {
        $roles = $this->enabledRoles($request);
        $role = $request->query('role');

        if (in_array($role, ['guru', 'staff'], true)) {
            return redirect()->route($role === 'guru' ? 'admin.guru.index' : 'admin.staff.index');
        }

        if ($this->isWakasekRole($role)) {
            $viewRole = self::WAKASEK_GROUP;
            $filterRoles = [User::ROLE_WAKASEK];
        } elseif (in_array($role, $roles, true)) {
            $viewRole = $role;
            $filterRoles = [$role];
        } else {
            $viewRole = $roles[0] ?? 'ortu';
            $filterRoles = [$viewRole];
        }

        $users = User::query()
            ->whereIn('role', $filterRoles)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pengguna.index', compact('users', 'role', 'roles', 'viewRole'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $roles = $this->enabledRoles($request);
        $role = $request->query('role');

        if (in_array($role, ['guru', 'staff'], true)) {
            return redirect()->route($role === 'guru' ? 'admin.guru.create' : 'admin.staff.create');
        }

        if ($this->isWakasekRole($role)) {
            $role = self::WAKASEK_GROUP;
        }

        if (! in_array($role, $roles)) {
            $role = $roles[0] ?? 'ortu';
        }

        return view('admin.pengguna.create', ['role' => $role, 'roles' => $roles]);
    }

    public function store(Request $request): RedirectResponse
    {
        $roles = $this->enabledRoles($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'in:'.implode(',', $roles)],
            'position' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:L,P'],
        ]);

        if ($validated['role'] === self::WAKASEK_GROUP) {
            $validated['role'] = User::ROLE_WAKASEK;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['school_id'] = $request->user()->school_id;
        $validated['created_by'] = $request->user()->id;

        User::create($validated);

        $backRole = $this->isWakasekRole($validated['role']) ? self::WAKASEK_GROUP : $validated['role'];

        return redirect()
            ->route('admin.pengguna.index', ['role' => $backRole])
            ->with('status', $request->user()->school?->roleLabel($validated['role']) . ' berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.pengguna.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $roles = $this->enabledRoles($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', 'in:'.implode(',', $roles)],
            'position' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:L,P'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (! empty($validated['role']) && $validated['role'] === self::WAKASEK_GROUP) {
            $validated['role'] = User::ROLE_WAKASEK;
        }

        $user->update($validated);

        $backRole = $this->isWakasekRole($user->role) ? self::WAKASEK_GROUP : $user->role;

        return redirect()
            ->route('admin.pengguna.index', ['role' => $backRole])
            ->with('status', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $role = $user->role;

        $user->delete();

        $backRole = $this->isWakasekRole($role) ? self::WAKASEK_GROUP : $role;

        return redirect()
            ->route('admin.pengguna.index', ['role' => $backRole])
            ->with('status', 'Akun berhasil dihapus.');
    }
}
