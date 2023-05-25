<?php

namespace App\Filters;

use Illuminate\Http\Request;

class ApiFilter {
  protected $safeParms = [];

  protected $fieldsToAny = [];

  protected $columnMap = [];

  protected $operatorMap = [
    'eq' => '=',
    'lt' => '<',
    'lte' => '<=',
    'gt' => '>',
    'gte' => '>=',
    'ne' => '!=',
    'like' => 'LIKE',
    'null' => '<>',
  ];

  public function transform(Request $request) {
    $eloQuery = [];
    $anyFilterQuery = [];


    foreach ($this->safeParms as $parm => $operators) {
      $query = $request->query($parm);

      if (!isset($query)) {
        continue;
      }

      $column = $this->columnMap[$parm] ?? $parm;

      foreach ($operators as $operator) {
        if (isset($query[$operator])) {
          if ($operator == 'like') {
            $eloQuery[] = [$column, $this->operatorMap[$operator], '%'.$query[$operator].'%'];
          }
          else if ($operator == 'null') {
            $eloQuery[] = [
              $column,
              $query[$operator] == 'false' ? $this->operatorMap[$operator] : '=',
              $query[$operator] == 'false' ? '' : null
            ];
          }
          else {
            $eloQuery[] = [$column, $this->operatorMap[$operator], $query[$operator]];
          }
        }
      }
    }


    $anyQuery = $request->query('any');
    if (isset($anyQuery)) {

      foreach ($this->fieldsToAny as $field) {
        $query = $request->query($field);

        if (!isset($query)) {
          $column = $this->columnMap[$field] ?? $field;

          $anyFilterQuery[] = [$column, 'LIKE', '%'.$anyQuery.'%', 'or'];
        }
      }

    }

    return [$eloQuery, $anyFilterQuery];
  }
}