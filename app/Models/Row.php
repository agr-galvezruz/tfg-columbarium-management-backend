<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    use HasFactory;

    protected $fillable = [
      'internal_code',
      'description',
      'room_id'
    ];

    public function room() {
      return $this->belongsTo(Room::class);
    }

    public function niches() {
      return $this->hasMany(Niche::class);
    }
}
