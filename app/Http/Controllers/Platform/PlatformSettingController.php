<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformSettingController extends Controller
{
    public function index(): View
    {
        return view('platform.settings', ['settings' => PlatformSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = PlatformSetting::current();

        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:100'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $oldLogo = $settings->logo;
            $path = $request->file('logo')->store('logos', 'public');
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }
            $validated['logo'] = $path;
        } else {
            unset($validated['logo']);
        }

        $settings->update($validated);

        return redirect()->route('platform.settings.index')->with('status', 'Pengaturan platform berhasil disimpan.');
    }
}