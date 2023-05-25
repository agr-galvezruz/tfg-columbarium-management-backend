<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class UserFilter extends ApiFilter {
  protected $safeParms = [
    'id' => ['like'],
    'rol' => ['eq']
  ];

  protected $fieldsToAny = [
    'id',
    'rol',
  ];
}