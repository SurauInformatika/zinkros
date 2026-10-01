<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolPlanLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function dashboard(): View
    {
        $schoolCounts = School::query()
            ->selectRaw('plan, COUNT(*) as total')
            ->groupBy('plan')
            ->pluck('total', 'plan')
            ->all();

        $planStats = collect(config('plans.order', []))
            ->map(fn (string $key): array => [
                'key' => $key,
                'label' => config('plans.plans.'.$key.'.label', $key),
                'price' => (int) config('plans.plans.'.$key.'.price', 0),
                'count' => (int) ($schoolCounts[$key] ?? 0),
            ])
            ->values()
            ->all();

        $subscribed = School::whereIn('status', [School::STATUS_ACTIVE, School::STATUS_TRIAL])
            ->get(['plan']);

        $stats = [
            'total' => School::count(),
            'active' => School::where('status', School::STATUS_ACTIVE)->count(),
            'trial' => School::where('status', School::STATUS_TRIAL)->count(),
            'suspended' => School::where('status', School::STATUS_SUSPENDED)->count(),
            'revenue' => Payment::where('status', Payment::STATUS_ACTIVE)->sum('amount'),
            'subscribed' => $subscribed->count(),
            'monthly_potential' => $subscribed->reduce(
                fn (int $sum, School $s) => $sum + (int) config('plans.plans.'.$s->plan.'.price', 0),
                0,
            ),
        ];

        $attention = School::all()
            ->filter(fn (School $s): bool => $s->attentionReason() !== null)
            ->values();

        $recentSchools = School::latest()->limit(5)->get();

        return view('platform.dashboard', compact('stats', 'planStats', 'attention', 'recentSchools'));
    }

    public function schools(Request $request): View
    {
        $query = School::query();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        $schools = $query
            ->withCount(['users', 'students'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = [School::STATUS_TRIAL, School::STATUS_ACTIVE, School::STATUS_SUSPENDED, School::STATUS_EXPIRED];

        return view('platform.schools.index', compact('schools', 'statuses'));
    }

    public function show(School $school): View
    {
        $school->loadCount(['users', 'students', 'classes', 'subjects']);
        $school->load('suspendedBy');

        $payments = $school->payments()
            ->with('creator')
            ->latest('paid_at')
            ->get();

        $planLogs = $school->planLogs()
            ->with('createdBy')
            ->latest()
            ->limit(20)
            ->get();

        return view('platform.schools.show', compact('school', 'payments', 'planLogs'));
    }

    public function activate(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'plan' => ['nullable', Rule::in(array_keys(config('plans.plans')))],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $now = now();

        Payment::create([
            'school_id' => $school->id,
            'amount' => $validated['amount'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'paid_at' => $now,
            'note' => $validated['note'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $previousPlan = $school->plan;
        $previousStatus = $school->status;
        $newPlan = $validated['plan'] ?? $previousPlan ?? School::PLAN_PRO;

        $school->update([
            'status' => School::STATUS_ACTIVE,
            'plan' => $newPlan,
            'subscribed_at' => $now,
            'next_billing_at' => Carbon::parse($validated['period_end'])->addDay(),
        ]);

        SchoolPlanLog::create([
            'school_id' => $school->id,
            'from_plan' => $previousPlan,
            'to_plan' => $newPlan,
            'from_status' => $previousStatus,
            'to_status' => School::STATUS_ACTIVE,
            'note' => $validated['note'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('status', 'Pembayaran dicatat, paket '.$school->planLabel().', dan sekolah diaktifkan.');
    }

    public function changePlan(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => ['required', Rule::in(array_keys(config('plans.plans')))],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $previous = $school->plan;

        if ($previous !== $validated['plan']) {
            $school->update(['plan' => $validated['plan']]);

            SchoolPlanLog::create([
                'school_id' => $school->id,
                'from_plan' => $previous,
                'to_plan' => $validated['plan'],
                'note' => $validated['note'] ?? null,
                'created_by' => auth()->id(),
            ]);
        }

        return back()->with('status', 'Paket sekolah diubah menjadi '.config('plans.plans.'.$validated['plan'].'.label').'.');
    }

    public function suspend(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'suspended_reason' => ['required', 'string', 'max:500'],
        ]);

        $school->update([
            'status' => School::STATUS_SUSPENDED,
            'suspended_at' => now(),
            'suspended_reason' => $validated['suspended_reason'],
            'suspended_by' => auth()->id(),
        ]);

        return back()->with('status', 'Sekolah berhasil ditangguhkan.');
    }

    public function unsuspend(School $school): RedirectResponse
    {
        $school->update([
            'status' => School::STATUS_ACTIVE,
            'suspended_at' => null,
            'suspended_reason' => null,
            'suspended_by' => null,
        ]);

        return back()->with('status', 'Akses sekolah berhasil dipulihkan.');
    }

    public function extendTrial(School $school): RedirectResponse
    {
        $school->update([
            'status' => School::STATUS_TRIAL,
            'trial_ends_at' => Carbon::now()->addDays(14),
        ]);

        return back()->with('status', 'Masa trial diperpanjang 14 hari.');
    }

    public function voidPayment(Request $request, School $school, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:500'],
        ]);

        $payment->update([
            'status' => Payment::STATUS_VOID,
            'void_reason' => $validated['void_reason'],
            'voided_at' => now(),
            'voided_by' => auth()->id(),
        ]);

        return back()->with('status', 'Pembayaran dibatalkan.');
    }

    public function updateNote(Request $request, School $school, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $payment->update(['note' => $validated['note'] ?? null]);

        return back()->with('status', 'Catatan pembayaran diperbarui.');
    }
}
