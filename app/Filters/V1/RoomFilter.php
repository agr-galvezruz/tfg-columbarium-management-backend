<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class RoomFilter extends ApiFilter {
  protected $safeParms = [
    'internalCode' => ['like'],
    'location' => ['like'],
    'description' => ['like']
  ];

  protected $columnMap = [
    'internalCode' => 'internal_code'
  ];

  protected $fieldsToAny = [
    'internalCode',
    'location',
    'description'
  ];
}