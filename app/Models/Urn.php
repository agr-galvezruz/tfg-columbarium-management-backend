<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Urn extends Model
{
    use HasFactory;

    protected $fillable = [
      'internal_code',
      'status',
      'description',
      'niche_id'
    ];

    public function niche() {
      return $this->belongsTo(Niche::class);
    }

    public function relocations() {
      return $this->hasMany(Relocation::class);
    }

    public function reservations() {
      return $this->hasMany(Reservation::class);
    }
}
