<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class CasketFilter extends ApiFilter {
  protected $safeParms = [
    'description' => ['like']
  ];

  // protected $columnMap = [
  //   'internalCode' => 'internal_code'
  // ];

  protected $fieldsToAny = [
    // 'description'
  ];
}