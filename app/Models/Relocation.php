<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relocation extends Model
{
    use HasFactory;

    protected $fillable = [
      'start_date',
      'end_date',
      'description',
      'urn_id',
      'casket_id'
    ];

    public function urn() {
      return $this->belongsTo(Urn::class);
    }

    public function casket() {
      return $this->belongsTo(Casket::class);
    }
}
