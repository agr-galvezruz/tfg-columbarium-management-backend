<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class DepositFilter extends ApiFilter {
  protected $safeParms = [
    'startDate' => ['like'],
    'endDate' => ['like'],
    'description' => ['like'],
    'deceasedRelationship' => ['like'],
  ];

  protected $columnMap = [
    'startDate' => 'start_date',
    'endDate' => 'end_date',
    'deceasedRelationship' => 'deceased_relationship'
  ];

  protected $fieldsToAny = [
    // 'startDate',
    // 'endDate',
    // 'description'
  ];
}