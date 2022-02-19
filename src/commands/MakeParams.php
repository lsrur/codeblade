<?php

namespace Lsrur\Codeblade\Commands;

class MakeParams
{
  private $attributes;

  public function __construct($options = '')
  {
    $this->attributes = collect(explode(',', $options))
        ->mapWithKeys(function ($v) {
          return [
            \Str::before($v, '=') => (\Str::contains($v, '=') ? \Str::after($v, '=') : true)
          ];
        })
        ->toArray();
  }

  public function getAll()
  {
    return $this->attributes;
  }

  public function __get($key)
  {
    $getter = 'get' . ucFirst($key);
    return method_exists($this, $getter)
      ? $this->$getter()
      : $this->attributes[$key] ?? null;
  }
}
