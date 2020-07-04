<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Table
{
    private $columns = [];
    private $rows = [];

    /**
    * Add column
    */
    public function addColumn(string $c)
    {
      $this->columns[] = $c;
    }

    /**
    * Add row
    */
    public function addRow(array $r)
    {
      $this->rows[] = $r;
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
