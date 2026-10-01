<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformContentController extends Controller
{
    public function hero(): View
    {
        return view('platform.content.hero', ['settings' => PlatformSetting::current()]);
    }

    public function updateHero(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_badge' => ['nullable', 'string', 'max:100'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_title_highlight' => ['nullable', 'string', 'max:100'],
            'hero_desc' => ['nullable', 'string', 'max:1000'],
            'hero_microcopy' => ['nullable', 'string', 'max:255'],
            'hero_cta_guest' => ['nullable', 'string', 'max:100'],
            'hero_cta_features' => ['nullable', 'string', 'max:100'],
            'hero_cta_auth' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['show_hero'] = $request->boolean('show_hero');

        PlatformSetting::current()->update($validated);

        return redirect()->route('platform.content.hero')->with('status', 'Bagian hero berhasil disimpan.');
    }

    public function features(): View
    {
        return view('platform.content.features', ['settings' => PlatformSetting::current()]);
    }

    public function updateFeatures(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fitur_heading' => ['nullable', 'string', 'max:100'],
            'fitur_subtitle' => ['nullable', 'string', 'max:255'],
            'features' => ['nullable', 'array'],
            'features.*.icon' => ['required', 'string', Rule::in(array_keys(config('platform.feature_icons')))],
            'features.*.title' => ['required', 'string', 'max:100'],
            'features.*.desc' => ['required', 'string', 'max:255'],
        ]);

        if (array_key_exists('features', $validated)) {
            $validated['features'] = json_encode(array_values($validated['features']));
        }

        $validated['show_fitur'] = $request->boolean('show_fitur');

        PlatformSetting::current()->update($validated);

        return redirect()->route('platform.content.features')->with('status', 'Fitur utama berhasil disimpan.');
    }

    public function peran(): View
    {
        return view('platform.content.peran', ['settings' => PlatformSetting::current()]);
    }

    public function updatePeran(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'peran_heading' => ['nullable', 'string', 'max:150'],
            'peran_subtitle' => ['nullable', 'string', 'max:300'],
            'peran_roles' => ['nullable', 'array'],
            'peran_roles.*.icon' => ['required', 'string', Rule::in(array_keys(config('platform.feature_icons')))],
            'peran_roles.*.title' => ['required', 'string', 'max:100'],
            'peran_roles.*.desc' => ['required', 'string', 'max:255'],
        ]);

        if (array_key_exists('peran_roles', $validated)) {
            $validated['peran_roles'] = json_encode(array_values($validated['peran_roles']));
        }

        $validated['show_peran'] = $request->boolean('show_peran');

        PlatformSetting::current()->update($validated);

        return redirect()->route('platform.content.peran')->with('status', 'Section peran berhasil disimpan.');
    }

    public function tampilan(): View
    {
        return view('platform.content.tampilan', ['settings' => PlatformSetting::current()]);
    }

    public function updateTampilan(Request $request): RedirectResponse
    {
        $settings = PlatformSetting::current();

        $validated = $request->validate([
            'tampilan_heading' => ['nullable', 'string', 'max:150'],
            'tampilan_subtitle' => ['nullable', 'string', 'max:300'],
            'tampilan_shots' => ['nullable', 'array', 'max:8'],
            'tampilan_shots.*.icon' => ['required', 'string', Rule::in(array_keys(config('platform.feature_icons')))],
            'tampilan_shots.*.label' => ['required_with:tampilan_shots', 'string', 'max:100'],
            'tampilan_shots.*.caption' => ['required_with:tampilan_shots', 'string', 'max:200'],
            'tampilan_shots.*.image' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'tampilan_shots.*.remove_image' => ['nullable', 'boolean'],
        ], [
            'tampilan_shots.*.image.image' => 'File yang diunggah pada kartu Tampilan Aplikasi harus berupa gambar.',
            'tampilan_shots.*.image.max' => 'Gambar pada kartu Tampilan Aplikasi terlalu besar (maksimal 1MB).',
            'tampilan_shots.*.image.mimes' => 'Gambar pada kartu Tampilan Aplikasi harus berformat PNG, JPG, atau WEBP.',
        ]);

        if (array_key_exists('tampilan_shots', $validated)) {
            $existingShots = $settings->shots();
            $shots = [];
            foreach (array_values($validated['tampilan_shots']) as $key => $shot) {
                $current = $existingShots[$key] ?? [];
                $oldImage = $current['image'] ?? null;
                $newImage = $shot['image'] ?? null;

                if (! empty($shot['remove_image']) && $shot['remove_image'] !== '0') {
                    if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                    $shot['image'] = null;
                } elseif ($newImage instanceof UploadedFile) {
                    $shot['image'] = $newImage->store('platform/shots', 'public');
                    if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                } else {
                    $shot['image'] = $oldImage;
                }

                unset($shot['remove_image']);
                $shots[] = $shot;
            }
            $validated['tampilan_shots'] = json_encode($shots);
        }

        $validated['show_tampilan'] = $request->boolean('show_tampilan');

        $settings->update($validated);

        return redirect()->route('platform.content.tampilan')->with('status', 'Tampilan aplikasi berhasil disimpan.');
    }

    public function sekolah(): View
    {
        return view('platform.content.sekolah', ['settings' => PlatformSetting::current()]);
    }

    public function updateSekolah(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sekolah_heading' => ['nullable', 'string', 'max:150'],
            'sekolah_subtitle' => ['nullable', 'string', 'max:300'],
            'testi_heading' => ['nullable', 'string', 'max:150'],
            'testi_subtitle' => ['nullable', 'string', 'max:300'],
            'testimonials' => ['nullable', 'array'],
            'testimonials.*.quote' => ['required', 'string', 'max:1000'],
            'testimonials.*.author' => ['required', 'string', 'max:100'],
            'testimonials.*.role' => ['required', 'string', 'max:150'],
            'testimonials.*.initials' => ['required', 'string', 'max:4'],
        ]);

        $validated['show_sekolah'] = $request->boolean('show_sekolah');

        if (array_key_exists('testimonials', $validated)) {
            $validated['testimonials'] = json_encode(array_values($validated['testimonials']));
        }

        PlatformSetting::current()->update($validated);

        return redirect()->route('platform.content.sekolah')->with('status', 'Section sekolah & testimoni berhasil disimpan.');
    }

    public function footer(): View
    {
        return view('platform.content.footer', ['settings' => PlatformSetting::current()]);
    }

    public function updateFooter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'footer_tagline' => ['nullable', 'string', 'max:300'],
            'footer_address' => ['nullable', 'string', 'max:300'],
            'footer_phone' => ['nullable', 'string', 'max:50'],
            'footer_email' => ['nullable', 'email', 'max:150'],
            'footer_copyright' => ['nullable', 'string', 'max:200'],
            'footer_medsos' => ['nullable', 'array'],
            'footer_medsos.*.icon' => ['required', 'string', Rule::in(array_keys(config('platform.medsos_icons')))],
            'footer_medsos.*.label' => ['required', 'string', 'max:100'],
            'footer_medsos.*.url' => ['nullable', 'string', 'max:300'],
        ]);

        $validated['show_footer'] = $request->boolean('show_footer');

        if (array_key_exists('footer_medsos', $validated)) {
            $validated['footer_medsos'] = json_encode(array_values($validated['footer_medsos']));
        }

        PlatformSetting::current()->update($validated);

        return redirect()->route('platform.content.footer')->with('status', 'Footer berhasil disimpan.');
    }

    public function pricing(): View
    {
        return view('platform.content.pricing', ['settings' => PlatformSetting::current()]);
    }

    public function updatePricing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pricing_heading' => ['nullable', 'string', 'max:150'],
            'pricing_subtitle' => ['nullable', 'string', 'max:300'],
            'plans' => ['nullable', 'array', 'max:6'],
            'plans.*.name' => ['required', 'string', 'max:50'],
            'plans.*.tagline' => ['required', 'string', 'max:200'],
            'plans.*.price' => ['required', 'string', 'max:50'],
            'plans.*.period' => ['required', 'string', 'max:50'],
            'plans.*.badge' => ['nullable', 'string', 'max:25'],
            'plans.*.note' => ['nullable', 'string', 'max:120'],
            'plans.*.cta' => ['required', 'string', 'max:60'],
            'plans.*.accent' => ['required', 'string', Rule::in(['none', 'primary', 'red'])],
            'plans.*.features_lines' => ['nullable', 'string', 'max:3000'],
            'plans.*.excludes_lines' => ['nullable', 'string', 'max:3000'],
        ]);

        $validated['show_pricing'] = $request->boolean('show_pricing');

        if (array_key_exists('plans', $validated)) {
            $lines = fn (?string $value): array => array_values(array_filter(array_map(
                'trim',
                preg_split('/\r\n|\r|\n/', $value ?? '')
            ), fn (?string $line): bool => $line !== null && $line !== ''));

            $plans = [];
            foreach (array_values($validated['plans']) as $plan) {
                $plan['features'] = $lines($plan['features_lines'] ?? null);
                $plan['excludes'] = $lines($plan['excludes_lines'] ?? null);
                unset($plan['features_lines'], $plan['excludes_lines']);
                $plans[] = $plan;
            }

            $validated['pricing_plans'] = json_encode($plans);
            unset($validated['plans']);
        }

        PlatformSetting::current()->update($validated);

        return redirect()->route('platform.content.pricing')->with('status', 'Section paket harga berhasil disimpan.');
    }
}