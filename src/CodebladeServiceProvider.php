<?php

namespace Lsrur\Codeblade;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Lsrur\Codeblade\Commands\MakeCommand;
use Lsrur\Codeblade\Commands\InitCommand;

class CodebladeServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap the application services.
   */
  public function boot()
  {
    /*
     * Optional methods to load your package assets
     */
    // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'codeblade');
    // $this->loadViewsFrom(__DIR__.'/../resources/views', 'codeblade');
    // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    // $this->loadRoutesFrom(__DIR__.'/routes.php');

    Blade::directive('cbSaveAs', function ($expression) {
      $expression = \Str::of($expression)
        ->replace('/', DIRECTORY_SEPARATOR)
        ->replace('\\', DIRECTORY_SEPARATOR)
        ->value();
      return "<?php \Config::set('cb_save_as', {$expression});?>";
    });

    Blade::directive('cbRun', function ($expression) {
      $expression = \Str::of($expression)
        ->remove('"')
        ->remove("'")
        ->value();

      return "<?php \Config::push('cb_run', '{$expression}');?>";
    });

    Str::macro('plurals', function ($str) {
      $result = [static::plural($str)];

      // Spanish rule
      if (!in_array(substr($str, -1), ['a', 'e', 'i', 'o', 'u'])) {
        $result[] = $str . 'es';
      }

      return $result;
    });

    if ($this->app->runningInConsole()) {
      $this->publishes([
        __DIR__ . '/../config/config.php' => config_path('codeblade.php'),
      ], 'config');

      // Publishing the views.
      /*$this->publishes([
          __DIR__.'/../resources/views' => resource_path('views/vendor/codeblade'),
      ], 'views');*/

      // Publishing assets.
      /*$this->publishes([
          __DIR__.'/../resources/assets' => public_path('vendor/codeblade'),
      ], 'assets');*/

      // Publishing the translation files.
      /*$this->publishes([
          __DIR__.'/../resources/lang' => resource_path('lang/vendor/codeblade'),
      ], 'lang');*/

      // Registering package commands.
      $this->commands([
        MakeCommand::class,
        InitCommand::class,
      ]);
    }
  }

  /**
   * Register the application services.
   */
  public function register()
  {
    // Automatically apply the package configuration
    $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'codeblade');

    // Register the main class to use with the facade
    $this->app->singleton('codeblade', function () {
      return new Codeblade;
    });
  }
}
