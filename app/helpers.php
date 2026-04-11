<?php

/*
|--------------------------------------------------------------------------
| Global helpers
|--------------------------------------------------------------------------
|
| Functions registered via composer.json "autoload.files". Add new helpers
| here guarded with function_exists() to avoid redeclaration errors during
| tests and class discovery.
|
*/

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {
    /**
     * Return a site setting value, falling back to $default when the key
     * is absent or the settings table is unavailable.
     *
     * The full settings collection is loaded once per process via a permanent
     * cache key (`settings.all`). The cache is invalidated by the Setting
     * model's saved/deleted events — callers never need to touch it manually.
     *
     * @param string $key     Dot-notation setting key, e.g. 'blog.public_creation'
     * @param mixed  $default Fallback value if the setting is missing
     */
    function setting(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings.all', function () {
            return Setting::all()->keyBy('key');
        });

        $setting = $all->get($key);

        return $setting?->casted_value ?? $default;
    }
}
