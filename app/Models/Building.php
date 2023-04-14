<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
      'internal_code',
      'name',
      'address',
      'description'
    ];

    public function rooms() {
      return $this->hasMany(Room::class);
    }
}
