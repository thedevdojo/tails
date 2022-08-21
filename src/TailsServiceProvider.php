<?php

namespace Devdojo\Tails;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Facades\Devdojo\Tails\Tails;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class TailsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        // Load tails views and routes
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tails');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('tails.php'),
            ], 'tails');

            // Registering tails commands.
            $this->commands([
                Commands\Clear::class,
                Commands\Ping::class
            ]);
        }

        // Default @tails directive
        Blade::directive('tails', function ($projectString) {

            [$data, $project, $projectPage, $key] = Tails::getProjectDataFromString($projectString);

            Tails::storeBladeFile( $project, $projectPage, $data, $key );
            $includeFile = config('tails.view_folder') . '.' . $project . '.' . $projectPage;
            if($key != 'html'){
                $includeFile .= '.' . $key;
            }

            return '<?php echo \Blade::render("@include(\'' . $includeFile . '\')"); ?>';
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
