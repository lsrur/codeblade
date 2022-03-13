<?php

namespace Lsrur\Codeblade\Commands;

use Illuminate\Console\Command;

class InitCommand extends Command
{
  protected $signature = 'codeblade:install';

  protected $description = 'Install codeblade';

  /**
  * Command handler
  */
  public function handle()
  {
    // Create codeblade folder & copy samples
    $dest = base_path('codeblade' . DIRECTORY_SEPARATOR . 'samples');

    if (!is_dir($dest)) {
      mkdir($dest, 0777, true);
    };

    $src = base_path('vendor/lsrur/codeblade/samples');
    $dir = opendir($src);
    while ($file = readdir($dir)) {
      if ($file != '.' & $file != '..') {
        $this->info('Copying sample template to ' . $dest . DIRECTORY_SEPARATOR . $file);
        copy(
          $src . DIRECTORY_SEPARATOR . $file,
          $dest . DIRECTORY_SEPARATOR . $file
        );
      }
    }
  }
}
