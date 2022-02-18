<?php

namespace Lsrur\Codeblade\Schema;

class TableSchema
{
  public static function getAdapterInstance($tableName)
  {
    if (env('DB_CONNECTION') == 'mysql') {
      return new MysqlTableSchema($tableName);
    }

    // TODO: PgsqlTableSchema
    die(env('DB_CONNECTION') . ' not supported' . PHP_EOL);
  }

  public static function __callStatic($method, $arguments)
  {
    return static::getAdapterInstance($arguments[0])->$method();
  }
}
