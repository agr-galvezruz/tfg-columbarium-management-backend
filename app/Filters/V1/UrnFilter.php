<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class UrnFilter extends ApiFilter {
  protected $safeParms = [
    'internalCode' => ['like'],
    'status' => ['eq'],
    'description' => ['like']
  ];

  protected $columnMap = [
    'internalCode' => 'internal_code',
  ];

  protected $fieldsToAny = [
    'internalCode',
    'description'
  ];
}