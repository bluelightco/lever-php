<?php

namespace Bluelightco\LeverPhp\Providers;

use Bluelightco\LeverPhp\Http\Client\LeverClient;
use Illuminate\Support\ServiceProvider;

class LeverServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LeverClient::class, function ($app) {
            return new LeverClient();
        });
    }

    public function boot()
    {
        // Publish with: php artisan vendor:publish --provider="Bluelightco\LeverPhp\Providers\LeverServiceProvider" --tag="config"
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('lever-php.php'),
        ], 'config');
    }
}
