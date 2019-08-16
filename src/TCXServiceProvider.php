<?php
/**
 * Created by PhpStorm.
 * User: Dodi
 * Date: 7/20/2018
 * Time: 10:37 AM
 */

namespace Verzth\TCXClient;


use Illuminate\Support\ServiceProvider;

class TCXServiceProvider extends ServiceProvider{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'tcx-client');

        $this->app->bind('Verzth\TCXClient', function ($app) {
            $options = $app['config']->get('tcx-client');
            return new TCX($options);
        });

        /*if(file_exists($this->helperPath())){
            require_once($this->helperPath());
        }*/
    }

    /**
     * Add the middleware to the router.
     *
     */
    public function boot(){
        $this->publishes([$this->configPath() => $this->app->basePath().'/config/tcx-client.php']);
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/tcx-client.php';
    }
    /*protected function helperPath()
    {
        return __DIR__ . '/Helpers/tcx.php';
    }*/

    /**
     * Create a pattern for a wildcard, based on Str::is() from Laravel
     *
     * @see https://github.com/laravel/framework/blob/5.5/src/Illuminate/Support/Str.php
     * @param $pattern
     * @return string
     */
    protected function convertWildcardToPattern($pattern)
    {
        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);

        return '#^'.$pattern.'\z#u';
    }
}