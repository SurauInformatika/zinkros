<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'address', 'phone', 'email', 'education_level', 'role_labels', 'pengguna_roles', 'reading_catalog_customized', 'logo', 'primary_color', 'secondary_color', 'plan', 'status', 'trial_ends_at', 'subscribed_at', 'next_billing_at', 'suspended_at', 'suspended_reason', 'suspended_by'])]
class School extends Model
{
    use HasUuids;

    public const PLAN_FREE = 'free';

    public const PLAN_BASIC = 'basic';

    public const PLAN_PRO = 'pro';

    public const PLAN_PRO_MAX = 'pro-max';

    public const STATUS_TRIAL = 'trial';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_EXPIRED = 'expired';

    /**
     * Roles that count toward the "guru" quota.
     */
    public const STAFF_ROLES = ['admin', 'guru', 'staff', 'keuangan', 'kepsek', 'wakasek'];

    /**
     * Master list of school education levels (keys stable for storage).
     */
    public static function educationLevels(): array
    {
        return [
            'kb' => ['label' => 'KB', 'levels' => ['KLP_A' => 'Kelompok A', 'KLP_B' => 'Kelompok B']],
            'tk' => ['label' => 'TK', 'levels' => ['TK_A' => 'TK A', 'TK_B' => 'TK B']],
            'sd' => ['label' => 'SD', 'levels' => self::numberedLevels(1, 6)],
            'mi' => ['label' => 'MI', 'levels' => self::numberedLevels(1, 6)],
            'smp' => ['label' => 'SMP', 'levels' => self::numberedLevels(7, 9)],
            'mts' => ['label' => 'MTs', 'levels' => self::numberedLevels(7, 9)],
            'sma' => ['label' => 'SMA', 'levels' => self::numberedLevels(10, 12)],
            'smk' => ['label' => 'SMK', 'levels' => self::numberedLevels(10, 12)],
            'ma' => ['label' => 'MA', 'levels' => self::numberedLevels(10, 12)],
            'slb' => ['label' => 'SLB', 'levels' => self::numberedLevels(1, 6)],
        ];
    }

    private static function numberedLevels(int $from, int $to): array
    {
        $levels = [];

        foreach (range($from, $to) as $g) {
            $levels[(string) $g] = 'Kelas '.$g;
        }

        return $levels;
    }

    public static function educationLevelLabel(string $key): string
    {
        return self::educationLevels()[$key]['label'] ?? $key;
    }

    /**
     * Grade-level option groups for the class form, narrowed to the given
     * education level, or all known levels when none was chosen.
     */
    public static function gradeOptionGroups(?string $educationLevel): array
    {
        $all = self::educationLevels();

        if ($educationLevel && isset($all[$educationLevel])) {
            return [
                ['label' => $all[$educationLevel]['label'], 'levels' => $all[$educationLevel]['levels']],
            ];
        }

        return collect($all)
            ->map(fn (array $meta): array => ['label' => $meta['label'], 'levels' => $meta['levels']])
            ->values()
            ->all();
    }

    /**
     * Every accepted grade-level value, used when a school has no
     * education level set yet (backward compatibility).
     */
    public static function allGradeLevelValues(): array
    {
        $values = [];

        foreach (self::educationLevels() as $meta) {
            foreach (array_keys($meta['levels']) as $value) {
                $values[$value] = $value;
            }
        }

        return array_values($values);
    }

    protected function casts(): array
    {
        return [
            'role_labels' => 'array',
            'pengguna_roles' => 'array',
            'reading_catalog_customized' => 'boolean',
            'trial_ends_at' => 'datetime',
            'subscribed_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    /**
     * Role keys shown in the admin "Pengguna" menu.
     * Both their display term and visibility are customizable per school.
     */
    public const PENGGUNA_ROLES = ['kepsek', 'wakasek', 'guru', 'staff', 'ortu', 'keuangan'];

    private const FIXED_ROLE_LABELS = [
        'guru' => 'Guru',
        'staff' => 'Staff',
        'admin' => 'Admin',
    ];

    /**
     * Default display terms for the roles in the "Pengguna" menu.
     * These can be customized per school via the role_labels setting.
     */
    public static function defaultRoleLabels(): array
    {
        return [
            'kepsek' => 'Kepala Sekolah',
            'wakasek' => 'Wakil Kepala Sekolah',
            'guru' => 'Guru',
            'staff' => 'Staff',
            'ortu' => 'Orang Tua',
            'keuangan' => 'Keuangan',
        ];
    }

    /**
     * Display label for a role, honoring this school's own terminology
     * (e.g. "Mudir", "Direktur") with a default fallback.
     */
    public function roleLabel(string $role): string
    {
        if (in_array($role, self::PENGGUNA_ROLES, true)) {
            $labels = $this->role_labels ?? [];

            if (! empty($labels[$role])) {
                return $labels[$role];
            }

            return static::defaultRoleLabels()[$role] ?? $role;
        }

        return self::FIXED_ROLE_LABELS[$role] ?? $role;
    }

    /**
     * The pengguna roles currently enabled for display in the admin menu.
     * Roles can hidden via the settings; all are enabled by default.
     */
    public function penggunaRoles(): array
    {
        $enabled = $this->pengguna_roles ?? [];

        return array_values(array_filter(
            self::PENGGUNA_ROLES,
            fn (string $role): bool => $enabled[$role] ?? true
        ));
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function planLogs(): HasMany
    {
        return $this->hasMany(SchoolPlanLog::class);
    }

    public function academicCalendars(): HasMany
    {
        return $this->hasMany(AcademicCalendar::class);
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    public function isPro(): bool
    {
        return $this->plan === self::PLAN_PRO;
    }

    public function isFree(): bool
    {
        return $this->plan === self::PLAN_FREE;
    }

    /**
     * All accepted plan keys, driven by config/plans.php.
     */
    public static function planKeys(): array
    {
        return array_keys(config('plans.plans', []));
    }

    /**
     * Config array for the currently selected plan (label, price, quota, ...).
     */
    public function planConfig(): array
    {
        return config('plans.plans.'.($this->plan ?? ''), []);
    }

    public function planLabel(): string
    {
        return $this->planConfig()['label'] ?? ucfirst((string) $this->plan);
    }

    /**
     * 0-based tier position following config('plans.order').
     */
    public function planTier(): int
    {
        $tier = array_search($this->plan, config('plans.order', []), true);

        return $tier === false ? -1 : $tier;
    }

    /**
     * Whether this plan includes the named feature (cumulative by tier).
     */
    public function planHas(string $feature): bool
    {
        $meta = config('plans.features.'.$feature);

        if (! is_array($meta) || ! isset($meta['from'])) {
            return false;
        }

        $fromTier = array_search($meta['from'], config('plans.order', []), true);

        return $fromTier !== false && $this->planTier() >= $fromTier;
    }

    /**
     * Quota for the given key (siswa, guru, unit). null = unlimited.
     */
    public function quota(string $key): ?int
    {
        $quota = $this->planConfig()['quota'] ?? [];

        if (! array_key_exists($key, $quota)) {
            return null;
        }

        return $quota[$key];
    }

    /**
     * Count of records currently admitted against the given quota key.
     */
    public function quotaUsed(string $key): int
    {
        if ($key === 'siswa') {
            return $this->students()->count();
        }

        if ($key === 'guru') {
            return $this->users()->whereIn('role', self::STAFF_ROLES)->count();
        }

        return 0;
    }

    /**
     * Records still available for the given quota key, or null when unlimited.
     */
    public function quotaRemaining(string $key): ?int
    {
        $quota = $this->quota($key);

        return $quota === null ? null : max(0, $quota - $this->quotaUsed($key));
    }

    /**
     * Guard that admits $increment new records unless the plan quota is full.
     *
     * @throws \App\Exceptions\QuotaExceededException
     */
    public function assertWithinQuota(string $key, int $increment = 1): void
    {
        $quota = $this->quota($key);

        if ($quota === null || $this->quotaUsed($key) + $increment <= $quota) {
            return;
        }

        throw new \App\Exceptions\QuotaExceededException($this, $key, $increment, $quota);
    }

    /**
     * Every feature with its availability for the current plan.
     */
    public function featuresWithAccess(): array
    {
        $items = [];

        foreach (config('plans.features', []) as $key => $meta) {
            $items[$key] = [
                'key' => $key,
                'label' => $meta['label'] ?? $key,
                'description' => $meta['description'] ?? '',
                'from' => $meta['from'] ?? 'free',
                'included' => $this->planHas($key),
            ];
        }

        return $items;
    }

    public function isTrialExpired(): bool
    {
        return $this->isTrial() && $this->trial_ends_at?->isPast();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * A human-readable reason when this school needs follow-up, else null.
     */
    public function attentionReason(): ?string
    {
        if ($this->isExpired()) {
            return 'Status berakhir — perlu pengaktifan ulang.';
        }

        if ($this->isTrialExpired()) {
            return $this->isInGracePeriod()
                ? 'Trial selesai, masa tenggang tersisa '.$this->gracePeriodDaysLeft().' hari.'
                : 'Trial selesai tanpa pembayaran.';
        }

        if ($this->isActive() && $this->next_billing_at?->isPast()) {
            return $this->isInGracePeriod()
                ? 'Melewati tagihan, masa tenggang tersisa '.$this->gracePeriodDaysLeft().' hari.'
                : 'Pembayaran belum diterima setelah masa tenggang.';
        }

        return null;
    }

    /**
     * Banner notice for the school's own admin: a warning well before the
     * trial/billing expires, then a danger notice once it is overdue.
     * Returns ['level' => 'warning'|'danger', 'title', 'message'] or null.
     */
    public function billingNotice(): ?array
    {
        $now = now();
        $threshold = (int) config('plans.notice_days', 7);
        $graceLeft = $this->gracePeriodDaysLeft();

        if ($this->isSuspended()) {
            return [
                'level' => 'danger',
                'title' => 'Akses sekolah ditangguhkan',
                'message' => 'Hubungi pengelola platform untuk memulihkan akses.',
            ];
        }

        if ($this->isExpired()) {
            return [
                'level' => 'danger',
                'title' => 'Masa berlangganan berakhir',
                'message' => 'Hubungi pengelola platform untuk mengaktifkan kembali paket Anda.',
            ];
        }

        if ($this->isTrial()) {
            $ends = $this->trial_ends_at;

            if ($ends && $ends->isPast()) {
                return $graceLeft !== null
                    ? ['level' => 'danger', 'title' => 'Masa trial telah berakhir', 'message' => 'Masa tenggang tersisa '.$graceLeft.' hari. Hubungi pengelola platform untuk memperpanjang trial Anda.']
                    : ['level' => 'danger', 'title' => 'Masa trial telah berakhir', 'message' => 'Hubungi pengelola platform untuk memperpanjang trial Anda.'];
            }

            if ($ends && (int) $now->diffInDays($ends, false) <= $threshold) {
                return ['level' => 'warning', 'title' => 'Trial hampir berakhir', 'message' => 'Masa percobaan berakhir pada '.$ends->format('d M Y').'. Hubungi pengelola platform untuk konfirmasi langganan.'];
            }

            return null;
        }

        if ($this->isActive()) {
            $billing = $this->next_billing_at;

            if ($billing && $billing->isPast()) {
                return $graceLeft !== null
                    ? ['level' => 'danger', 'title' => 'Pembayaran belum diterima', 'message' => 'Masa tenggang tersisa '.$graceLeft.' hari. Silakan hubungi pengelola platform.']
                    : ['level' => 'danger', 'title' => 'Pembayaran belum diterima', 'message' => 'Masa tenggang telah habis. Silakan hubungi pengelola platform.'];
            }

            if ($billing && (int) $now->diffInDays($billing, false) <= $threshold) {
                return ['level' => 'warning', 'title' => 'Tagihan akan segera jatuh tempo', 'message' => 'Tagihan berikutnya pada '.$billing->format('d M Y').'. Silakan hubungi pengelola platform untuk pembayaran.'];
            }
        }

        return null;
    }

    private function graceDays(): int
    {
        return (int) config('plans.grace_days', 7);
    }

    public function isInGracePeriod(): bool
    {
        $now = now();

        if ($this->isTrial() && $this->trial_ends_at?->isPast()) {
            return $this->trial_ends_at->diffInDays($now) <= $this->graceDays();
        }

        if ($this->isActive() && $this->next_billing_at?->isPast()) {
            return $this->next_billing_at->diffInDays($now) <= $this->graceDays();
        }

        return false;
    }

    public function gracePeriodDaysLeft(): ?int
    {
        $now = now();

        if ($this->isTrial() && $this->trial_ends_at?->isPast()) {
            $days = $this->graceDays() - (int) $this->trial_ends_at->diffInDays($now);

            return max(0, $days);
        }

        if ($this->isActive() && $this->next_billing_at?->isPast()) {
            $days = $this->graceDays() - (int) $this->next_billing_at->diffInDays($now);

            return max(0, $days);
        }

        return null;
    }
}
