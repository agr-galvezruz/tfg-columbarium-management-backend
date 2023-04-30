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
      'casket_id',
      'deceased_relationship'
    ];

    public function person() {
      return $this->belongsTo(Person::class);
    }

    public function casket() {
      return $this->belongsTo(Casket::class);
    }

    public function reservation() {
      return $this->belongsTo(Reservation::class);
    }
}
