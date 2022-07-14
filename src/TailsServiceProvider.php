<?php

namespace Devdojo\Tails;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Facades\Devdojo\Tails\Tails;

class TailsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tails');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tails');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('tails.php'),
            ], 'tails');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/tails'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/tails'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/tails'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                Commands\Clear::class,
                Commands\Ping::class
            ]);
        }

        Blade::directive('tails_page', function($variable){
            return '<?php echo \Blade::render("@tails(' . $variable . ':html)"); ?>';
        });

        Blade::directive('tails', function ($projectString) {
            
            $projectStringTrimmed = trim(trim($projectString, "'"), '"');            
            $key = Tails::getKeyFromProjectString($projectStringTrimmed);
            $projectStringWithoutKey = str_replace(':' . $key, '', $projectStringTrimmed);

            $projectArray = explode('.', $projectStringWithoutKey);
            $project = $projectArray[0];
            $projectPage = '';
            
            if(isset($projectArray[1])){
                $projectPage = $projectArray[1];
            }

            $projectURL = $project;
            if(!empty($projectPage)){
                $projectURL = $project . '/' . $projectPage;
            }

            $response = Tails::getResponse($projectURL);
            $data = Tails::getDataFromResponse($key, $response);
            
            //return $data;
            $data = str_replace("'", "\'", $data);
            $data = str_replace('"', '\"', $data);
            return '<?php echo \Blade::render("' . $data . '"); ?>';
        });

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'tails');

        // Register the main class to use with the facade
        $this->app->singleton('tails', function () {
            return new Tails;
        });

        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Tails', "Devdojo\\Tails\\Tails");
    }
}
