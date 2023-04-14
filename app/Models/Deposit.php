<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
      'start_date',
      'end_date',
      'description',
      'reservation_id',
      'person_id',
      'casket_id'
    ];

    public function person() {
      return $this->belongsTo(Person::class);
    }

    public function urn() {
      return $this->belongsTo(Urn::class);
    }

    public function reservation() {
      return $this->hasOne(Reservation::class);
    }
}
