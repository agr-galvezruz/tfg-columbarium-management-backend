<?php

namespace Database\Seeders;

use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      DB::table('provinces')->delete();

      DB::table('provinces')->insert([
        ['id' => '1', 'name' => 'Albacete', 'created_at' => new DateTime(), 'updated_at' => new DateTime],
        ['id' => '2', 'name' => 'Ciudad Real', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '3', 'name' => 'Cuenca', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '4', 'name' => 'Guadalajara', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '5', 'name' => 'Toledo', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '6', 'name' => 'Huesca', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '7', 'name' => 'Teruel', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '8', 'name' => 'Zaragoza', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '9', 'name' => 'Ceuta', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '10', 'name' => 'Madrid', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '11', 'name' => 'Murcia', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '12', 'name' => 'Melilla', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '13', 'name' => 'Navarra', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '14', 'name' => 'Almería', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '15', 'name' => 'Cádiz', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '16', 'name' => 'Córdoba', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '17', 'name' => 'Granada', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '18', 'name' => 'Huelva', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '19', 'name' => 'Jaén', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '20', 'name' => 'Málaga', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '21', 'name' => 'Sevilla', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '22', 'name' => 'Asturias', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '23', 'name' => 'Cantabria', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '24', 'name' => 'Ávila', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '25', 'name' => 'Burgos', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '26', 'name' => 'León', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '27', 'name' => 'Palencia', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '28', 'name' => 'Salamanca', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '29', 'name' => 'Segovia', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '30', 'name' => 'Soria', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '31', 'name' => 'Valladolid', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '32', 'name' => 'Zamora', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '33', 'name' => 'Barcelona', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '34', 'name' => 'Gerona', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '35', 'name' => 'Lérida', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '36', 'name' => 'Tarragona', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '37', 'name' => 'Badajoz', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '38', 'name' => 'Cáceres', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '39', 'name' => 'Coruña, La', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '40', 'name' => 'Lugo', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '41', 'name' => 'Orense', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '42', 'name' => 'Pontevedra', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '43', 'name' => 'Rioja, La', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '44', 'name' => 'Baleares, Islas', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '45', 'name' => 'Álava', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '46', 'name' => 'Guipúzcoa', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '47', 'name' => 'Vizcaya', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '48', 'name' => 'Palmas, Las', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '49', 'name' => 'Tenerife, Santa Cruz De', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '50', 'name' => 'Alicante', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '51', 'name' => 'Castellón', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ['id' => '52', 'name' => 'Valencia', 'created_at' => new DateTime, 'updated_at' => new DateTime]
      ]);
    }
}
