<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $table = 'platform_settings';

    protected $fillable = [
        'app_name',
        'tagline',
        'logo',
        'primary_color',
        'secondary_color',
        'hero_badge',
        'hero_title',
        'hero_title_highlight',
        'hero_desc',
        'hero_microcopy',
        'hero_cta_guest',
        'hero_cta_features',
        'hero_cta_auth',
        'fitur_heading',
        'fitur_subtitle',
        'features',
        'tampilan_heading',
        'tampilan_subtitle',
        'tampilan_shots',
        'peran_heading',
        'peran_subtitle',
        'peran_roles',
        'show_hero',
        'show_fitur',
        'show_tampilan',
        'show_peran',
        'show_sekolah',
        'sekolah_heading',
        'sekolah_subtitle',
        'testi_heading',
        'testi_subtitle',
        'testimonials',
        'show_footer',
        'footer_tagline',
        'footer_address',
        'footer_phone',
        'footer_email',
        'footer_copyright',
        'footer_medsos',
        'show_pricing',
        'pricing_heading',
        'pricing_subtitle',
        'pricing_plans',
    ];

    protected static ?self $cache = null;

    public static function current(): self
    {
        if (static::$cache === null) {
            static::$cache = static::first() ?? static::query()->create([
                'app_name' => config('app.name'),
                'primary_color' => '#059669',
                'secondary_color' => '#0d9488',
            ]);
        }

        return static::$cache;
    }

    public static function forget(): void
    {
        static::$cache = null;
    }

    public static function appName(): string
    {
        return (string) (static::current()->app_name ?: config('app.name'));
    }

    public static function tagline(): ?string
    {
        return static::current()->tagline ?: null;
    }

    public static function hero(string $key, string $default): string
    {
        return (string) (static::current()->{$key} ?: $default);
    }

    public static function shots(): array
    {
        $shots = static::current()->tampilan_shots;

        if (is_string($shots)) {
            $shots = json_decode($shots, true);
        }

        if (! is_array($shots) || $shots === []) {
            return config('platform.tampilan_defaults');
        }

        return array_values($shots);
    }

    public static function features(): array
    {
        $features = static::current()->features;

        if (is_string($features)) {
            $features = json_decode($features, true);
        }

        if (! is_array($features) || $features === []) {
            return config('platform.feature_defaults');
        }

        return array_values($features);
    }

    public static function roles(): array
    {
        $roles = static::current()->peran_roles;

        if (is_string($roles)) {
            $roles = json_decode($roles, true);
        }

        if (! is_array($roles) || $roles === []) {
            return config('platform.peran_defaults');
        }

        return array_values($roles);
    }

    public static function showSection(string $key): bool
    {
        return (bool) (static::current()->{'show_' . $key} ?? true);
    }

    public static function testimonials(): array
    {
        $testimonials = static::current()->testimonials;

        if (is_string($testimonials)) {
            $testimonials = json_decode($testimonials, true);
        }

        if (! is_array($testimonials) || $testimonials === []) {
            $testimonials = config('platform.testimonial_defaults');
        }

        return array_map(function (array $testimonial): array {
            $testimonial['quote'] = str_replace('{app_name}', static::appName(), $testimonial['quote'] ?? '');

            return $testimonial;
        }, array_values($testimonials));
    }

    public static function footerMedsos(): array
    {
        $medsos = static::current()->footer_medsos;

        if (is_string($medsos)) {
            $medsos = json_decode($medsos, true);
        }

        if (! is_array($medsos) || $medsos === []) {
            return config('platform.footer_medsos_defaults');
        }

        return array_values($medsos);
    }

    public static function plans(): array
    {
        $plans = static::current()->pricing_plans;

        if (is_string($plans)) {
            $plans = json_decode($plans, true);
        }

        if (! is_array($plans) || $plans === []) {
            return config('platform.pricing_defaults');
        }

        return array_values($plans);
    }

    public static function pricingHeading(): string
    {
        return (string) (static::current()->pricing_heading ?: config('platform.pricing_heading', 'Paket Harga'));
    }

    public static function pricingSubtitle(): string
    {
        return (string) (static::current()->pricing_subtitle ?: config('platform.pricing_subtitle', 'Pilih paket yang sesuai dengan kebutuhan sekolah Anda.'));
    }

    public static function footerTagline(): string
    {
        return (string) (static::current()->footer_tagline ?: config('platform.footer_tagline', ''));
    }

    public static function footerCopyright(): string
    {
        $copyright = static::current()->footer_copyright;

        if (! empty($copyright)) {
            return (string) $copyright;
        }

        return '&copy; ' . date('Y') . ' ' . static::appName() . '. Hak cipta dilindungi.';
    }

    public static function primaryColor(): string
    {
        return (string) (static::current()->primary_color ?: '#059669');
    }

    public static function secondaryColor(): string
    {
        return (string) (static::current()->secondary_color ?: '#0d9488');
    }

    public static function logo(): ?string
    {
        $logo = static::current()->logo;

        return $logo ?: null;
    }

    public static function darken(string $hex, float $factor): string
    {
        $hex = ltrim($hex, '#');
        $dark = fn (int $c): int => (int) round(hexdec(substr($hex, $c, 2)) * $factor);

        return sprintf('#%02x%02x%02x', min(255, $dark(0)), min(255, $dark(2)), min(255, $dark(4)));
    }
}