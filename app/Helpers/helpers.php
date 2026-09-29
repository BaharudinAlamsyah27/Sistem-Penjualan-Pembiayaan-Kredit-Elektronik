<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get or set setting value.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return app(Setting::class);
        }

        return Setting::get($key, $default);
    }
}
