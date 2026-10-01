<?php

namespace App\Support;

use App\Models\School;

class PlanMenu
{
    /**
     * Remove sidebar items (recursively) whose route belongs to a feature
     * not available on the given plan. Items without a route are kept.
     */
    public static function filter(array $links, School $school): array
    {
        $result = [];

        foreach ($links as $link) {
            if (isset($link['children'])) {
                $children = self::filter($link['children'], $school);

                if ($children !== []) {
                    $link['children'] = $children;
                    $result[] = $link;
                }

                continue;
            }

            if (! isset($link['route'])) {
                $result[] = $link;

                continue;
            }

            $feature = static::featureForRoute($link['route']);

            if ($feature === null || $school->planHas($feature)) {
                $result[] = $link;
            }
        }

        return $result;
    }

    /**
     * The feature that gates the given route name (route prefix), or null
     * when the route is free for every plan.
     */
    public static function featureForRoute(string $route): ?string
    {
        foreach (config('plans.feature_routes', []) as $feature => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($route, $prefix)) {
                    return $feature;
                }
            }
        }

        return null;
    }
}