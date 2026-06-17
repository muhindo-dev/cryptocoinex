<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Telescope is a dev-only dependency — register its providers only when
        // the package is actually installed (local), so production --no-dev
        // installs work without it.
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(\App\Providers\TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        // Cap indexed string columns at 191 chars so utf8mb4 indexes stay within
        // the 1000-byte key limit on older MySQL/MariaDB builds.
        Schema::defaultStringLength(191);

        // Practice simulator — keep passwords simple. Minimum 4 characters,
        // no other restrictions. Applies everywhere Password::defaults() is used
        // (register, reset, password change, API).
        Password::defaults(fn () => Password::min(4));

        // Use our themed pagination view for every ->links() call (admin + trade),
        // instead of the default unstyled Tailwind markup.
        Paginator::defaultView('pagination::simple-default');
        Paginator::defaultSimpleView('pagination::simple-default');

        // Force the root URL so url() / route() helpers generate correct
        // paths when the app is served from a subdirectory (e.g. /onyx/).
        if ($root = config('app.url')) {
            URL::forceRootUrl($root);

            // Also force HTTPS scheme when APP_URL starts with https
            if (str_starts_with($root, 'https://')) {
                URL::forceScheme('https');
            }
        }
    }
}
