<?php

namespace Lsrur\Codeblade\Schema;

class DictTable
{
  private $tableName;
  private $fields;
  private $relations;

  public function __construct(string $tableName)
  {
    $this->tableName = $tableName;

    $this->fields = TableSchema::getFields($tableName)
      ->whereNotIn('name', ['created_at', 'updated_at', 'deleted_at'])
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
    return $this->tableName;
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
    return collect($this->fields)->map(function ($f) {
      return $f->name;
    });
  }

  public function getSingular()
  {
    return \Str::singular($this->tableName);
  }

  public function getModelName()
  {
    return \Str::of($this->tableName)
      ->singular()
      ->studly()
      ->value();
  }

  public function getTimestamps()
  {
    return collect($this->getFieldNames())
      ->intersect(['updated_at', 'created_at'])
      ->count() == 2;
  }

  public function __get($key)
  {
    $getter = 'get' . ucFirst($key);
    return method_exists($this, $getter)
      ? $this->$getter()
      : $this->tableDict[$key] ?? null;
  }
}
