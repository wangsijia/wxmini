<?php
namespace Wangsijia\Wxmini;

use Illuminate\Support\ServiceProvider;

class WxminiServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $config_file = __DIR__ . '/../config/config.php';

        $this->mergeConfigFrom($config_file, 'wxmini');

        $this->publishes([
            $config_file => config_path('wxmini.php')
        ], 'wxmini');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('wxmini', function ()
        {
            return new Wxmini();
        });

        $this->app->alias('wxmini', Wxmini::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['wxmini', Wxmini::class];
    }
}
