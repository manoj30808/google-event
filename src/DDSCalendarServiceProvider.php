<?php namespace MspPack\DDSCalendar;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Router;
use Spatie\GoogleCalendar\Event as GEvent;

class DDSCalendarServiceProvider extends ServiceProvider {


    //protected $defer = false;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        
        $this->loadMigrationsFrom(__DIR__.'/database');

        $this->publishes([
            __DIR__.'/resources' => resource_path('views'),
            __DIR__.'/database/seeds' => database_path('seeds'),
        ]);
        $this->publishes([
            __DIR__.'/public' => public_path(),
        ]);

        \Schema::defaultStringLength(191);

    }
    public function register()
    {
        $this->app->register('Spatie\GoogleCalendar\GoogleCalendarServiceProvider');
        /*
         * Create aliases for the dependency.
         */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('GoogleCalendar', 'Spatie\GoogleCalendar\GoogleCalendarFacade');
    }
}
