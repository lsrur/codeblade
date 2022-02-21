<?php

namespace Lsrur\Codeblade\Commands;

use Illuminate\Console\Command;
use Lsrur\Codeblade\Schema\DictTable;
use Lsrur\Codeblade\Schema\TableSchema;
use Illuminate\Support\Facades\Blade;

class MakeCommand extends Command
{
  protected $signature = 'codeblade:make
    {template : Template name in dot notation (samples.model)}
    {table : Table name (multiple tables separated by comma w/o spaces) }
    {--params= : Parameters to be passed to the template }
    {--copy : Copy to clipboard insted writing files}
    {--force : Force overwrite}';

  protected $description = 'Generate code through templates';

  private $toClipboard;
  private $params;

  /**
   * Find & retrieve template content
   */
  private function openTemplate($template)
  {
    // Build filename from template name
    // Ex: samples.controller -> samples/controller.blade.php
    $template = \Str::of($template)
      ->replace('.', DIRECTORY_SEPARATOR)
      ->finish('.blade.php')
      ->value();

    // Search for the file among the template folders
    $templateFile = '';
    foreach (\Config::get('codeblade.template_folders', []) as $folder) {
      if (file_exists($folder . DIRECTORY_SEPARATOR . $template)) {
        $templateFile = $folder . DIRECTORY_SEPARATOR . $template;
        break;
      }
    };

    if (empty($templateFile)) {
      $this->error('Template not found: ' . $template);
      die();
    }

    return file_get_contents($templateFile);
  }

  /**
   * Copy generated code to clipboard
   */
  private function copyToClipboard()
  {
    // Write content on temp file
    $fileName = tempnam(sys_get_temp_dir(), 'codeblade');
    file_put_contents($fileName, $this->toClipboard);

    // Prepare shell command from config file
    $cmd = \Str::replace(
      '{file}',
      $fileName,
      config('codeblade.pbcopy_command', 'pbcopy < {file}')
    );

    // Exec command and get termination
    $res = null;
    $out = [];
    exec($cmd, $out, $res);
    if ($res === 0) {
      $this->info('Generated code copied to the clipboard');
    } else {
      $this->error("Check the key 'cbcopy_command' in config/codeblade.php");
    }
  }

  /**
  * Save generated content to file
  */
  private function saveOutput($content)
  {
    $fileName = \Config::get('cb_save_as');
    if (empty($fileName)) {
      $this->error('No output filename specified. Use the @saveAs directive in the template or copy to clipboard option.');
      return;
    }

    // Overwrite control
    if (file_exists($fileName) && !$this->option('force')) {
      if (!$this->confirm("{$fileName} already exists, overwrite?", false)) {
        $this->line("Canceled : {$fileName}");
        return;
      }
    }

    // Ensure folder exists or create it
    $pathInfo = pathinfo($fileName);
    if (!is_dir($pathInfo['dirname'])) {
      mkdir($pathInfo['dirname'], 0777, true);
    }

    // If output filename ext is php but not blade.php add <?php header
    if (($pathInfo['extension'] ?? '') == 'php' && !\Str::endsWith($pathInfo['basename'], 'blade.php')) {
      $content = '<?php' . PHP_EOL . PHP_EOL . $content;
    }

    file_put_contents($fileName, $content);

    $this->info("Generated: {$fileName}");
  }

  /**
  * Code generation
  */
  private function generate($table, $template)
  {
    // Get template content
    $blade = $this->openTemplate($template);

    \Config::set('cb_save_as', null);
    \Config::set('cb_run', []);

    // Get table data dictionary
    $tableDict = new DictTable($table);
    // Let's code!
    $result = Blade::render($blade, [
      'table' => $tableDict,
      'params' => $this->params,
      '__startCurlyBraces' => '{{',
      '__endCurlyBraces' => '}}'
    ], true);

    $fileName = \Config::get('cb_save_as');

    // Result could be empty, ex when a template only calls other templates
    if (!empty(trim($result))) {
      if ($this->option('copy') || empty($fileName)) {
        $this->toClipboard .= $result . PHP_EOL;
      } else {
        $this->saveOutput($result);
      }
    }

    // Run generator recursively if template makes cbRun calls
    foreach (\Config::get('cb_run', []) as $runTemplate) {
      $this->generate($table, $runTemplate);
    }
  }

  /**
  * Command handler
  */
  public function handle()
  {
    $this->params = new MakeParams($this->option('params'));
    $this->toClipboard = '';

    // Set blade views path to codeblade template paths
    // This way we can use @include in our templates
    \Config::set('view.paths', \Config::get('codeblade.template_folders'));

    // Iterate through tables and generate code
    $tables = explode(',', $this->argument('table'));
    foreach ($tables as $table) {
      if (!TableSchema::tableExists($table)) {
        $this->error("Table {$table} not found");
        continue;
      }
      $this->generate($table, $this->argument('template'));
    }

    // Copy content to clipboard if any
    if (!empty($this->toClipboard)) {
      $this->copyToClipboard();
    }
  }
}
