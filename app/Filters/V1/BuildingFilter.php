<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class BuildingFilter extends ApiFilter {
  protected $safeParms = [
    'internalCode' => ['like'],
    'name' => ['like'],
    'address' => ['like'],
    'description' => ['like']
  ];

  protected $columnMap = [
    'internalCode' => 'internal_code'
  ];

  protected $fieldsToAny = [
    'internalCode',
    'name',
    'address',
    'description'
  ];
}