<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Table
{
    private $name = 'Table';
    private $columns = [];
    private $rows = [];

    public function __construct(string $name)
    {
      $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
    * Add column
    */
    public function addColumn(string $c)
    {
      $this->columns[] = $c;
    }


    public function setColumns(array $columns)
    {
      $this->columns = $columns;
    }

    /**
    * Add row
    */
    public function addRow(array $r)
    {
      $this->rows[] = $r;
    }

    public function setRows(array $rows)
    {
      $this->rows = $rows;
    }

    /**
    * Get columns
    */
    public function getColumns(): array
    {
      return $this->columns;
    }

    /**
    * Get rows
    */
    public function getRows(): array
    {
      return $this->rows;
    }
}
