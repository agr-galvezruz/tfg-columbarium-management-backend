<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class PersonFilter extends ApiFilter {
  protected $safeParms = [
    'dni' => ['like'],
    'firstName' => ['like'],
    'lastName1' => ['like'],
    'lastName2' => ['like'],
    'address' => ['like'],
    'city' => ['like'],
    'state' => ['like'],
    'postalCode' => ['like'],
    'phone' => ['like'],
    'casketId' => ['null']
  ];

  protected $columnMap = [
    'firstName' => 'first_name',
    'lastName1' => 'last_name_1',
    'lastName2' => 'last_name_2',
    'postalCode' => 'postal_code',
    'casketId' => 'casket_id'
  ];

  protected $fieldsToAny = [
    'dni',
    'firstName',
    'lastName1',
    'lastName2',
    'address',
    'city',
    'state',
    'postalCode',
    'phone'
  ];
}