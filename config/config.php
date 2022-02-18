<?php
return [
  //   'enabled' => env('APP_ENV', 'local') == 'local',
  'enabled' => true,

  'template_folders' => [
    '/Users/lautarosrur/Code/templates',
    base_path('codeblade')
  ],

  'cbcopy_command' => 'pbcopy < {file}'
];
