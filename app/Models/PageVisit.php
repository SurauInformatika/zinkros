<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageVisit extends Model
{
    protected $fillable = [
        'page_type',
        'page_id',
        'ip_address',
        'user_agent',
        'referrer',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'page_id');
    }

    public static function track(string $pageType, ?string $pageId = null): void
    {
        $visit = request();
        self::create([
            'page_type' => $pageType,
            'page_id' => $pageId,
            'ip_address' => $visit->ip(),
            'user_agent' => substr($visit->userAgent(), 0, 255),
            'referrer' => $visit->headers->get('referer'),
            'visited_at' => now(),
        ]);
    }

    public static function countVisits(string $pageType, ?string $pageId = null, ?string $period = null): int
    {
        $query = self::where('page_type', $pageType);

        if ($pageId) {
            $query->where('page_id', $pageId);
        }

        if ($period === 'today') {
            $query->whereDate('visited_at', today());
        } elseif ($period === 'week') {
            $query->where('visited_at', '>=', now()->startOfWeek());
        } elseif ($period === 'month') {
            $query->where('visited_at', '>=', now()->startOfMonth());
        }

        return $query->count();
    }

    public static function dailyVisits(string $pageType, ?string $pageId = null, int $days = 30): \Illuminate\Support\Collection
    {
        $query = self::where('page_type', $pageType)
            ->where('visited_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date');

        if ($pageId) {
            $query->where('page_id', $pageId);
        }

        return $query->get();
    }
}
