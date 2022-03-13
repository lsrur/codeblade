<?php

namespace Lsrur\Codeblade\Schema;

class DictRelation
{
  private $attributes;

  public function __construct($attr)
  {
    $this->attributes = $attr;
  }

  public function __get($key)
  {
    $getter = 'get' . ucFirst($key);
    return method_exists($this, $getter)
      ? $this->$getter()
      : $this->attributes[$key] ?? $this->attributes['__' . $key] ?? null;
  }
}
