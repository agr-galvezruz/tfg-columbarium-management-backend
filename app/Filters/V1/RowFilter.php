<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class RowFilter extends ApiFilter {
  protected $safeParms = [
    'internalCode' => ['like'],
    'description' => ['like']
  ];

  protected $columnMap = [
    'internalCode' => 'internal_code'
  ];

  protected $fieldsToAny = [
    'internalCode',
    'description'
  ];
}