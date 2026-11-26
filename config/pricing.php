<?php

return [
    'stale_hours' => 24,
    // Trusted deployment configuration only. Provider must return LKR per 1 USD.
    'fx_url' => env('FX_RATE_URL'),
    'fx_json_path' => env('FX_RATE_JSON_PATH', 'rates.LKR'),
    'fx_source' => env('FX_RATE_SOURCE', 'Configured FX provider'),
];
