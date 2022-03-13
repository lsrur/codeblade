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

  public function getReferences()
  {
    return \Str::of($this->attributes['references']);
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

    return $this->attributes['cast']
      ?? $baseTypeCast[$this->base_type]
      ?? null;
  }

  public function getFaker()
  {
    if ($this->is_foreign) {
      return "randomElement(\DB::table('{$this->references}')->pluck('{$this->on}'))";
    }

    $baseTypeFaker = [
      'string' => 'word()',
      'text' => 'sentence()',
      'integer' => 'randomNumber(5, false)',
      'boolean' => 'randomElement([true,false])',
      'enum' => "randomElement(['" . implode("','", $this->enum_options ?? []) . "'])",
      'set' => "randomElements(['" . implode("','", $this->enum_options ?? []) . "'])",
      'json' => 'randomElement([])',
      'decimal' => 'randomFloat(2)',
      'date' => 'date("Y-m-d")',
      'datetime' => 'date("Y-m-d H:m:s")',
    ];

    return $this->attributes['faker']
      ?? $baseTypeFaker[$this->base_type]
      ?? 'word()';
  }

  public function getRule()
  {
    $baseTypeRule = [
      'string' => "string|max:{$this->size}",
      'text' => "string|max:{$this->size}",
      'integer' => 'numeric',
      'boolean' => 'boolean',
      'enum' => 'in:' . implode(',', $this->enum_options ?? []),
      'set' => 'in:' . implode(',', $this->enum_options ?? []),
      'json' => 'array',
      'decimal' => 'numeric',
      'date' => 'date',
      'datetime' => 'datetime',
    ];

    $foreign = $this->is_foreign
      ? 'exists:' . $this->references . ',' . $this->on
      : '';

    return $this->attributes['rule'] ?? implode('|', array_filter([
      $this->is_nullable ? 'nullable' : 'required',
      $baseTypeRule[$this->base_type],
      $foreign
    ]));
  }

  public function __get($key)
  {
    $getter = 'get' . ucFirst($key);
    return method_exists($this, $getter)
      ? $this->$getter()
      : $this->attributes[$key] ?? null;
  }
}
