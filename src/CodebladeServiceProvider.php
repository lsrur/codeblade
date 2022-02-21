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
    Blade::directive('start', function ($expression) {
      return '{{$s}}';
    });

    Blade::directive('cbCurly', function ($expression) {
      return '{{$__startCurlyBraces}}' . $expression . '{{$__endCurlyBraces}}';
    });

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
    $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'codeblade');
  }
}
