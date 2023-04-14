<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Casket extends Model
{
    use HasFactory;

    protected $fillable = [
      'description'
    ];

    public function people() {
      return $this->hasMany(Person::class);
    }

    public function relocations() {
      return $this->hasMany(Relocation::class);
    }

    public function deposits() {
      return $this->hasMany(Deposit::class);
    }
}
