<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/26
 * Time: 18:21
 */

namespace Junhai\Rabc;


use Illuminate\Support\ServiceProvider;

class RabcServiceProvider extends  ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('rabc.php'),
        ]);

        // Register commands
        $this->commands('command.rabc.migration');

    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRabc();

        $this->registerCommands();

        $this->mergeConfig();
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerRabc()
    {
        $this->app->singleton('rabc', function ($app) {
            return new Rabc($app);
        });

        $this->app->alias('rabc', 'Junhai\Rabc\Rabc');
    }


    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->singleton('command.rabc.migration', function ($app) {
            return new MigrationCommand();
        });
    }

    /**
     * Merges user's and entrust's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'rabc'
        );
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.rabc.migration'
        ];
    }
}
