<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class RelocationFilter extends ApiFilter {
  protected $safeParms = [
    'startDate' => ['like'],
    'endDate' => ['like'],
    'description' => ['like'],
  ];

  protected $columnMap = [
    'startDate' => 'start_date',
    'endDate' => 'end_date',
  ];

  protected $fieldsToAny = [
    // 'date',
    // 'description'
  ];
}