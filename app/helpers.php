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
     * Two-layer caching:
     *
     * 1. **Per-request memo** via the service container — one effective
     *    lookup per HTTP request, no matter how many times setting() is
     *    called. The container binding is flushed between requests
     *    automatically by Laravel.
     *
     * 2. **Persistent cache** via `Cache::rememberForever('settings.all')`
     *    survives across requests on the configured cache store. We store
     *    a plain associative array of `[key => ['type', 'value']]` rather
     *    than an Eloquent Collection to avoid deserialization "incomplete
     *    object" errors on the database cache driver.
     *
     * Both layers are invalidated automatically by the Setting model's
     * saved/deleted events (see App\Models\Setting::booted()).
     *
     * @param string $key     Dot-notation setting key, e.g. 'blog.public_creation'
     * @param mixed  $default Fallback value if the setting is missing
     */
    function setting(string $key, mixed $default = null): mixed
    {
        if (app()->bound('settings.memo')) {
            $all = app('settings.memo');
        } else {
            // First call in this request — resolve from persistent cache
            $all = Cache::rememberForever('settings.all', function () {
                return Setting::query()
                    ->get(['key', 'type', 'value'])
                    ->mapWithKeys(fn (Setting $s) => [
                        $s->key => [
                            'type'  => $s->type,
                            'value' => $s->value,
                        ],
                    ])
                    ->all();
            });

            app()->instance('settings.memo', $all);
        }

        if (! isset($all[$key])) {
            return $default;
        }

        $row = $all[$key];

        return match ($row['type']) {
            'bool'   => filter_var($row['value'], FILTER_VALIDATE_BOOLEAN),
            'string' => (string) $row['value'],
            default  => $row['value'],
        };
    }
}
