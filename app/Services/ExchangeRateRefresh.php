<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateRefresh
{
    public function refresh(): bool
    {
        if (! config('pricing.fx_url')) {
            return false;
        }
        try {
            $response = Http::timeout(10)->retry(2, 250)->get(config('pricing.fx_url'))->throw();
            $value = data_get($response->json(), config('pricing.fx_json_path'));
            $value = is_int($value) || is_float($value) || is_string($value) ? (string) $value : '';
            if (! preg_match('/^\d{1,8}(?:\.\d{1,6})?$/', $value) || BigDecimal::of($value)->isLessThanOrEqualTo(0)) {
                throw new \RuntimeException('Invalid LKR-per-USD rate.');
            }
            ExchangeRate::create(['lkr_per_usd' => $value, 'source' => config('pricing.fx_source'), 'fetched_at' => now(), 'is_override' => false]);

            return true;
        } catch (\Throwable $error) {
            Log::warning('FX refresh failed; retaining last successful rate.', ['error_type' => get_class($error)]);

            return false;
        }
    }
}
