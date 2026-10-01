<?php

namespace App\Console\Commands;

use App\Models\School;
use Illuminate\Console\Command;

class CheckSchoolExpiry extends Command
{
    protected $signature = 'school:check-expiry';

    protected $description = 'Check and update expired school statuses (trials and subscriptions)';

    public function handle(): int
    {
        $now = now();

        // Expire trials that have passed grace period (default 7 days after trial_ends_at)
        $expiredTrials = School::where('status', School::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', $now->copy()->subDays((int) config('plans.grace_days', 7)))
            ->update([
                'status' => School::STATUS_EXPIRED,
                'trial_ends_at' => null,
            ]);

        if ($expiredTrials > 0) {
            $this->info("Expired {$expiredTrials} trial school(s).");
        }

        // Expire active schools past billing + grace period (default 7 days after next_billing_at)
        $expiredActive = School::where('status', School::STATUS_ACTIVE)
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', $now->copy()->subDays((int) config('plans.grace_days', 7)))
            ->update([
                'status' => School::STATUS_EXPIRED,
                'next_billing_at' => null,
            ]);

        if ($expiredActive > 0) {
            $this->info("Expired {$expiredActive} active school(s) past billing grace period.");
        }

        // Log grace period schools (trial or active, past deadline but within grace days)
        $graceTrials = School::where('status', School::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', $now)
            ->where('trial_ends_at', '>', $now->copy()->subDays((int) config('plans.grace_days', 7)))
            ->count();

        $graceActive = School::where('status', School::STATUS_ACTIVE)
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', $now)
            ->where('next_billing_at', '>', $now->copy()->subDays((int) config('plans.grace_days', 7)))
            ->count();

        if ($graceTrials > 0) {
            $this->warn("{$graceTrials} trial school(s) in grace period.");
        }

        if ($graceActive > 0) {
            $this->warn("{$graceActive} active school(s) in billing grace period.");
        }

        $total = $expiredTrials + $expiredActive;

        if ($total === 0 && $graceTrials === 0 && $graceActive === 0) {
            $this->info("No schools affected.");
        }

        return self::SUCCESS;
    }
}
