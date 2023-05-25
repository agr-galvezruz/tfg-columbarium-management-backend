<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
      'dni',
      'first_name',
      'last_name_1',
      'last_name_2',
      'address',
      'city',
      'state',
      'postal_code',
      'phone',
      'email',
      'marital_status',
      'birthdate',
      'deathdate',
      'casket_id'
    ];

    public function casket() {
      return $this->belongsTo(Casket::class);
    }

    public function user() {
      return $this->belongsTo(User::class);
    }

    public function deposits() {
      return $this->hasMany(Deposit::class);
    }

    public function reservations() {
      return $this->hasMany(Reservation::class);
    }
}
