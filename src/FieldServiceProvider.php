<?php

namespace Bordieris\MapAddress;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/extended-address.php' => base_path('config/extended-address.php'),
            ], 'config');
        }

        Nova::serving(function (ServingNova $event) {
            Nova::script('extended_address_gmaps', $this->googleMapsSource());
            Nova::script('extended_address', __DIR__.'/../dist/js/field.js');
            Nova::style('extended_address', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/extended-address.php', 'extended-address');
    }

    private function googleMapsSource()
    {
        return vsprintf(
            'https://maps.googleapis.com/maps/api/js?key=%s&language=%s',
            [
                Config::get('map-address.api_key'),
                Config::get('map-address.language'),
            ]
        );
    }
}
