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

        // This directive is used inside of the resources/views/page.blade.php, and is the view that is loaded
        // when calling the Tails::get() route
        Blade::directive('tails_page', function($variable){
            return '<?php echo \Blade::render("@tails(' . $variable . ':html)"); ?>';
        });

        // Default @tails directive
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
            
            return "<?php echo \Blade::render($data); ?>";
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
