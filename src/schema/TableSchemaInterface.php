<?php

namespace Lsrur\Codeblade\Schema;

interface TableSchemaInterface
{
  public function __construct($tableName);

  public function tableExists();

  public function getFields();

  public function getRelations();
}
