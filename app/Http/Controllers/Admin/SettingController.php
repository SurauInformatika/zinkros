<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function profile(): View
    {
        $user = auth()->user();

        return view('admin.setting.profile', ['user' => $user]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return redirect()->route('admin.setting.profile')->with('status', 'Profil berhasil diperbarui.');
    }

    public function password(): View
    {
        return view('admin.setting.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.setting.password')->with('status', 'Password berhasil diubah.');
    }

    public function billing(): View
    {
        $school = auth()->user()->school;

        $stats = [
            'siswa' => $school->quotaUsed('siswa'),
            'guru' => $school->quotaUsed('guru'),
        ];

        return view('admin.setting.billing', [
            'school' => $school,
            'plans' => config('plans.plans', []),
            'features' => $school->featuresWithAccess(),
            'stats' => $stats,
            'payments' => $school->payments()->orderByDesc('paid_at')->limit(20)->get(),
        ]);
    }

    public function school(): View
    {
        $school = auth()->user()->school;

        return view('admin.setting.school', ['school' => $school]);
    }

    public function updateSchool(Request $request): RedirectResponse
    {
        $school = auth()->user()->school;

        $roles = array_keys(\App\Models\School::defaultRoleLabels());

        $roleLabelRules = [];
        $penggunaRoleRules = [];
        foreach ($roles as $roleKey) {
            $roleLabelRules['role_labels.'.$roleKey] = ['nullable', 'string', 'max:255'];
            $penggunaRoleRules['pengguna_roles.'.$roleKey] = ['nullable'];
        }

        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'education_level' => ['nullable', Rule::in(array_keys(\App\Models\School::educationLevels()))],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'role_labels' => ['nullable', 'array'],
            'pengguna_roles' => ['nullable', 'array'],
        ], $roleLabelRules, $penggunaRoleRules));

        if (filled($school->education_level)) {
            $validated['education_level'] = $school->education_level;
        }

        if (isset($validated['role_labels'])) {
            $validated['role_labels'] = collect($validated['role_labels'])
                ->map(fn ($label) => trim((string) $label))
                ->filter()
                ->all();
        } else {
            $validated['role_labels'] = $school->role_labels ?? [];
        }

        if ($request->has('pengguna_roles')) {
            $submittedRoles = $request->input('pengguna_roles', []);
            $validated['pengguna_roles'] = collect($roles)
                ->mapWithKeys(fn ($roleKey) => [$roleKey => in_array($roleKey, $submittedRoles, true)])
                ->all();
        } else {
            $validated['pengguna_roles'] = $school->pengguna_roles ?? collect($roles)
                ->mapWithKeys(fn ($roleKey) => [$roleKey => true])
                ->all();
        }

        if ($request->hasFile('logo')) {
            $oldLogo = $school->logo;
            $path = $request->file('logo')->store('logos', 'public');
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }
            $validated['logo'] = $path;
        } else {
            unset($validated['logo']);
        }

        $school->update($validated);

        return redirect()->route('admin.setting.school')->with('status', 'Pengaturan sekolah berhasil disimpan.');
    }
}
