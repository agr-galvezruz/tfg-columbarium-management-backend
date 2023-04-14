<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
      'start_date',
      'end_date',
      'description',
      'urn_id',
      'person_id'
    ];

    public function urn() {
      return $this->belongsTo(Urn::class);
    }

    public function person() {
      return $this->belongsTo(Person::class);
    }

    public function deposit() {
      return $this->hasOne(Deposit::class);
    }
}
