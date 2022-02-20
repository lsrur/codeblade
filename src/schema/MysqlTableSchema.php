<?php

namespace Lsrur\Codeblade\Schema;

class MysqlTableSchema implements TableSchemaInterface
{
  private $tableName;
  private $db;

  public function __construct($tableName)
  {
    $this->tableName = $tableName;
    $this->db = env('DB_DATABASE');
  }

  private function getBaseType($type)
  {
    $baseTypes = collect([
      'string' => ['char', 'varchar'],
      'text' => ['text', 'longtext', 'tinytext', 'mediumtext'],
      'integer' => ['int', 'smallint', 'mediumint', 'bigint'],
      'boolean' => ['tinyint'],
      'enum' => ['enum'],
      'set' => ['set'],
      'json' => ['json', 'jsonb'],
      'decimal' => ['decimal', 'float', 'double'],
      'date' => ['date'],
      'datetime' => ['datetime', 'timestamp'],
      'time' => ['time'],
      'binary' => ['blob', 'binary', 'longblob', 'mediumblob', 'tinyblob', 'varbinary'],
    ]);

    return $baseTypes->reduce(function ($c, $v, $k) use ($type) {
      return in_array($type, $v) ? $k : $c;
    }, 'generic');
  }

  private function getForeign($fieldName)
  {
    $sql = "SELECT * FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
            WHERE TABLE_SCHEMA= '{$this->db}'
            AND TABLE_NAME='{$this->tableName}'
            AND COLUMN_NAME='{$fieldName}'
            AND `REFERENCED_TABLE_NAME` IS NOT NULL";
    $foreign = \DB::select($sql);

    return [
      'is_foreign' => count($foreign) > 0,
      'references' => count($foreign) ? $foreign[0]->REFERENCED_TABLE_NAME : null,
      'on' => count($foreign) ? $foreign[0]->REFERENCED_COLUMN_NAME : null
    ];
  }

  private function getEnumOptions($field)
  {
    return $field->DATA_TYPE == 'enum' || $field->DATA_TYPE == 'set'
      ? \Str::of($field->COLUMN_TYPE)
          ->between('(', ')')
          ->remove("'")
          ->explode(',')
          ->toArray()
      : [];
  }

  public function getFields()
  {
    $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME='{$this->tableName}' AND TABLE_SCHEMA= '{$this->db}'
            ORDER BY TABLE_NAME, ORDINAL_POSITION";

    return collect(\DB::select($sql))->map(function ($item) {
      // Check foreign constraints on this field
      $foreign = $this->getForeign($item->COLUMN_NAME);

      // Extract metadata in column comment as key=value;key=value
      $meta = collect(explode(';', $item->COLUMN_COMMENT))
        ->mapWithKeys(function ($i) {
          return [trim(\Str::before($i, '=')) => trim(\Str::after($i, '='))];
        })
        ->filter()
        ->toArray();

      return array_merge($meta, [
        'name' => $item->COLUMN_NAME,
        'position' => $item->ORDINAL_POSITION,
        'is_primary' => $item->COLUMN_KEY == 'PRI',
        'is_autoincrement' => $item->EXTRA == 'auto_increment',
        'is_index' => $item->COLUMN_KEY == 'MUL',
        'default' => $item->COLUMN_DEFAULT,
        'is_nullable' => $item->IS_NULLABLE == 'YES',
        'type' => $item->DATA_TYPE,
        'size' => $item->CHARACTER_MAXIMUM_LENGTH ?? $item->NUMERIC_PRECISION,
        'scale' => $item->NUMERIC_SCALE,
        'comment' => $item->COLUMN_COMMENT,
        'base_type' => $this->getBaseType($item->DATA_TYPE),
        'enum_options' => $this->getEnumOptions($item),
        'is_foreign' => $foreign['is_foreign'],
        'references' => $foreign['references'],
        'on' => $foreign['on'],
      ]);
    });
  }

  public function tableExists()
  {
    $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA= '{$this->db}'
            AND TABLE_NAME = '{$this->tableName}'";
    return count(\DB::select($sql)) > 0;
  }

  public function findTable($tableName)
  {
    $tableNames = \Str::plurals($tableName);
    $tableNames = "'" . implode("','", $tableNames) . "'";
    $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA= '{$this->db}'
         AND TABLE_NAME IN ({$tableNames});";
    return count(\DB::select($sql)) > 0;
  }

  private function getPivotFields($relation)
  {
    // dd($relation);
  }

  private function isManyToMany($relation)
  {
    $tableName = $relation->TABLE_NAME;
    if (\Str::contains($tableName, '_')) {
      $first = \Str::before($tableName, '_');
      $second = \Str::after($tableName, '_');
      return $this->findTable($first) && $this->findTable($second);
    }
    return false;
  }

  public function getRelations()
  {
    $sql = "SELECT * FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
            WHERE TABLE_SCHEMA= '{$this->db}'
            AND REFERENCED_TABLE_NAME = '{$this->tableName}'";

    return collect(\DB::select($sql))->map(function ($relation) {
      $rel = [
        'local_key' => $relation->REFERENCED_COLUMN_NAME,
      ];
      if ($this->isManyToMany($relation)) {
        $rel['type'] = 'belongs_to_many';
        $rel['pivot'] = $this->getPivotFields($relation);
        $rel['model'] = \Str::of($relation->TABLE_NAME)
          ->replace(\Str::singular($this->tableName), '')
          ->singular()->studly()->value();
      } else {
        $rel['type'] = 'has_many';
        $rel['foreign_key'] = $relation->COLUMN_NAME;
        $rel['foreign_table'] = $relation->TABLE_NAME;
        $rel['model'] = \Str::of($relation->TABLE_NAME)
          ->singular()->studly()->value();
      }
      return new DictRelation($rel);
    })->toArray();
  }
}
