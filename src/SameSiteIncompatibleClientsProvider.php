<?php

namespace Skorp\SameSite;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Skorp\Dissua\SameSite;
use Skorp\SameSite\Middleware\SameSiteMiddleware;

class SameSiteIncompatibleClientsProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(__DIR__.'/config/samesite.php', 'samesite');

        $this->app->bind('SameSite', function () {
            return $this->app->make(SameSite::class);
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {

        $this->publishes([__DIR__.'/config/samesite.php' => config_path('samesite.php')],'config');

        $kernel = $this->app->make(Kernel::class);

        if (! $kernel->hasMiddleware(SameSiteMiddleware::class)) {

            if(!is_null($this->app['config']->get('samesite')['groups'])) {
                $groups = $this->app['config']->get('samesite')['groups'];
                foreach($groups as $group) {
                    $kernel->prependMiddlewareToGroup($group,SameSiteMiddleware::class);
                }
            }
        }
    }
}
