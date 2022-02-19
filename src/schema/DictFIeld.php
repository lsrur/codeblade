<?php

namespace Lsrur\Codeblade\Schema;

class DictField
{
  private $attributes;

  public function __construct($attributes)
  {
    $this->attributes = $attributes;
  }

  public function getName()
  {
    return \Str::of($this->attributes['name']);
  }

  public function getCast()
  {
    $baseTypeCast = [
      'boolean' => 'boolean',
      'date' => 'datetime',
      'decimal' => 'float',
      'json' => 'array',
      'date' => 'date',
      'datetime' => 'datetime',
      'time' => 'time'
    ];

    return $baseTypeCast[$this->base_type] ?? null;
  }

  public function getRule()
  {
    $baseTypeRule = [
      'string' => "string|max:{$this->size}",
      'text' => "string|max:{$this->size}",
      'integer' => 'numeric',
      'boolean' => 'boolean',
      'enum' => 'in:' . implode(',', $this->enum_options ?? []),
      'json' => 'array',
      'decimal' => 'numeric',
      'date' => 'date',
      'datetime' => 'datetime',
    ];

    return implode('|', [
      $this->nullable ? 'nullable' : 'required',
      $baseTypeRule[$this->base_type]
    ]);
  }

  public function __get($key)
  {
    $getter = 'get' . ucFirst($key);
    return method_exists($this, $getter)
      ? $this->$getter()
      : $this->attributes[$key] ?? null;
  }
}
