<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class NicheFilter extends ApiFilter {
  protected $safeParms = [
    'internalCode' => ['like'],
    'storageQuantity' => ['eq'],
    'description' => ['like']
  ];

  protected $columnMap = [
    'internalCode' => 'internal_code',
    'storageQuantity' => 'storage_quantity',
  ];

  protected $fieldsToAny = [
    'internalCode',
    'storageQuantity',
    'description'
  ];
}