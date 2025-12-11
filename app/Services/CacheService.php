<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    private const CARS_CACHE_PREFIX = 'cars_list';

    private const CACHE_KEYS_STORAGE = 'cars_list_keys';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Clear all cached cars list data
     * This method clears cache for all pages and user types
     */
    public function clearCarsCache(): void
    {
        $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget(self::CACHE_KEYS_STORAGE);
    }

    /**
     * Generate cache key for cars list with pagination
     */
    public function getCarCacheKey(int $page = 1, int $perPage = 12): string
    {
        $userType = auth()->check() ? 'admin' : 'guest';

        return self::CARS_CACHE_PREFIX."_{$userType}_page_{$page}_per_{$perPage}";
    }

    /**
     * Track cache key for easier invalidation
     */
    public function trackCacheKey(string $cacheKey): void
    {
        $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);

        if (! in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put(self::CACHE_KEYS_STORAGE, $keys, self::CACHE_TTL);
        }
    }
}
