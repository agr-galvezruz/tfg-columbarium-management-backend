<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Province;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Province\ProvinceCollection;

class ProvinceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      $buildings = Province::orderBy('name')->get();
      return new ProvinceCollection($buildings);
    }
}
