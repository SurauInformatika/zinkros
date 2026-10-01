<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\RecurringHoliday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringHolidayController extends Controller
{
    public function index(): View
    {
        $holidays = RecurringHoliday::orderBy('month')
            ->orderBy('day')
            ->get();

        return view('platform.kaldik.recurring-holidays.index', compact('holidays'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'month' => 'required|integer|min:1|max:12',
            'day' => 'nullable|integer|min:1|max:31',
            'calendar_type' => 'required|in:masehi,hijriah',
        ]);

        RecurringHoliday::create($validated);

        return redirect()->route('platform.recurring-holidays.index')
            ->with('status', 'Libur rutin berhasil ditambahkan.');
    }

    public function update(Request $request, RecurringHoliday $recurringHoliday): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'month' => 'required|integer|min:1|max:12',
            'day' => 'nullable|integer|min:1|max:31',
            'calendar_type' => 'required|in:masehi,hijriah',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $recurringHoliday->update($validated);

        return redirect()->route('platform.recurring-holidays.index')
            ->with('status', 'Libur rutin berhasil diperbarui.');
    }

    public function destroy(RecurringHoliday $recurringHoliday): RedirectResponse
    {
        $recurringHoliday->delete();

        return redirect()->route('platform.recurring-holidays.index')
            ->with('status', 'Libur rutin berhasil dihapus.');
    }
}
