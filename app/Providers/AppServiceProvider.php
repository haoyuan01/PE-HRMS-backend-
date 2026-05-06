<?php

namespace App\Providers;

use App\Constants\ConfigurationCodeConstants;
use App\Models\Configuration;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!Schema::hasTable('cache')) // prevent error if cache table doesn't exist during migration
        {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Rate Limiter - API Throttle Limit
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('auth', function ($request) {

            $limit = cache()->remember(ConfigurationCodeConstants::AUTH_RATE_LIMIT, 3600, function () {
                return Configuration::where('key', ConfigurationCodeConstants::AUTH_RATE_LIMIT)
                    ->value('value') ?? 30;
            });

            return Limit::perMinute($limit)->by(
                $request->user()?->id ?: $request->ip()
            );

        });

        /*
        |--------------------------------------------------------------------------
        | Sanctum Token Expiry
        |--------------------------------------------------------------------------
        */
        $bearer_token_expiry = cache()->remember(ConfigurationCodeConstants::AUTH_TOKEN_EXPIRY_DAYS, 3600, function () {
            return Configuration::where('key', ConfigurationCodeConstants::AUTH_TOKEN_EXPIRY_DAYS)
                ->value('value') ?? 7; // default as 7 days expiry
        });
        config(['sanctum.expiration' => $bearer_token_expiry * 24 * 60]);
    }
}
