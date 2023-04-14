<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Niche extends Model
{
    use HasFactory;

    protected $fillable = [
      'internal_code',
      'storage_quantity',
      'description',
      'row_id'
    ];

    public function row() {
      return $this->belongsTo(Row::class);
    }

    public function urns() {
      return $this->hasMany(Urn::class);
    }
}
