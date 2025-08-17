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
     *
     * @return void
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
     *
     * @param int $page
     * @param int $perPage
     * @return string
     */
    public function getCarCacheKey(int $page = 1, int $perPage = 12): string
    {
        $userType = auth()->check() ? 'admin' : 'guest';
        return self::CARS_CACHE_PREFIX . "_{$userType}_page_{$page}_per_{$perPage}";
    }

    /**
     * Track cache key for easier invalidation
     *
     * @param string $cacheKey
     * @return void
     */
    public function trackCacheKey(string $cacheKey): void
    {
        $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);

        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put(self::CACHE_KEYS_STORAGE, $keys, self::CACHE_TTL);
        }
    }

    /**
     * Get cache TTL value
     *
     * @return int
     */
    public function getCacheTTL(): int
    {
        return self::CACHE_TTL;
    }

    /**
     * Clear specific page cache
     *
     * @param int $page
     * @param int $perPage
     * @return void
     */
    public function clearPageCache(int $page, int $perPage = 12): void
    {
        $adminKey = self::CARS_CACHE_PREFIX . "_admin_page_{$page}_per_{$perPage}";
        $guestKey = self::CARS_CACHE_PREFIX . "_guest_page_{$page}_per_{$perPage}";

        Cache::forget($adminKey);
        Cache::forget($guestKey);
    }
}
