<?php

namespace Lsrur\Codeblade\Schema;

class DictTable
{
  private $tableName;
  public $timestamps;
  private $fields;
  private $relations;

  public function __construct(string $tableName)
  {
    $this->tableName = $tableName;
    $fieldSet = TableSchema::getFields($tableName);
    $this->timestamps = $fieldSet->whereIn('name', ['created_at', 'updated_at'])->count() == 2;

    $this->fields = $fieldSet->whereNotIn('name', ['created_at', 'updated_at', 'deleted_at'])
      ->map(function ($field) {
        return new DictField($field);
      });
    if (!count($this->fields)) {
      throw new \Exception('Table not found ' . $tableName);
    }

    $this->relations = TableSchema::getRelations($tableName);
  }

  public function getName()
  {
    return \Str::of($this->tableName);
  }

  public function getPrimaryKey()
  {
    return collect($this->fields)
      ->filter(function ($f) {
        return $f->is_primary;
      })
      ->map(function ($f) {
        return $f->name->value;
      })
      ->implode(',');
  }

  public function getFields()
  {
    return $this->fields;
  }

  public function getRelations()
  {
    return $this->relations;
  }

  public function getFieldNames()
  {
    return $this->fields->map(function ($f) {
      return $f->name->value();
    });
  }

  public function __get($key)
  {
    $getter = 'get' . ucFirst($key);
    return method_exists($this, $getter)
      ? $this->$getter()
      : $this->tableDict[$key] ?? null;
  }
}
