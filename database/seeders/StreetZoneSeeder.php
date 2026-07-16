<?php

namespace Database\Seeders;

use App\Models\StreetZone;
use Illuminate\Database\Seeder;

class StreetZoneSeeder extends Seeder
{
    public function run(): void
    {
        StreetZone::truncate();

        $zones = $this->data();

        foreach ($zones as $record) {
            StreetZone::create($record);
        }
    }

    private function data(): array
    {
        return [
            // ─────────────────────────────────────────────
            // ZONA CENTRO — $5.000 (Límites corregidos)
            // ─────────────────────────────────────────────
            ['street_name' => 'mitre',             'number_from' => 1,    'number_to' => 1900, 'price' => 5000],
            ['street_name' => 'moreno',            'number_from' => 1,    'number_to' => 1799, 'price' => 5000],
            ['street_name' => 'moreno',            'number_from' => 1800, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'elflein',           'number_from' => 1,    'number_to' => 1900, 'price' => 5000],
            ['street_name' => 'ada maria elflein', 'number_from' => 1,    'number_to' => 1900, 'price' => 5000],
            ['street_name' => 'gallardo',          'number_from' => 1,    'number_to' => 1900, 'price' => 5000],
            ['street_name' => 'angel gallardo',    'number_from' => 1,    'number_to' => 1900, 'price' => 5000],
            ['street_name' => 'tiscornia',         'number_from' => 1,    'number_to' => 1798, 'price' => 5000],
            ['street_name' => 'tiscornia',         'number_from' => 1799, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'antonio tiscornia', 'number_from' => 1,    'number_to' => 1798, 'price' => 5000],
            ['street_name' => 'antonio tiscornia', 'number_from' => 1799, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'anasagasti',        'number_from' => 1,    'number_to' => 1798, 'price' => 5000],
            ['street_name' => 'anasagasti',        'number_from' => 1799, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => '25 de mayo',        'number_from' => 1,    'number_to' => 1900, 'price' => 5000],
            ['street_name' => '25 de mayo',        'number_from' => 1901, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'oconnor',           'number_from' => 1,    'number_to' => 1900, 'price' => 5000],
            ['street_name' => 'o\'connor',         'number_from' => 1,    'number_to' => 1900, 'price' => 5000],
            ['street_name' => 'eduardo oconnor',   'number_from' => 1,    'number_to' => 1900, 'price' => 5000],

            // Calles enteras dentro de la zona Centro
            ['street_name' => '20 de febrero',     'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'san martin',        'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'libertad',          'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'buenos aires',      'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'juan manuel de rosas', 'number_from' => 1,    'number_to' => 2000, 'price' => 5000],
            ['street_name' => '20 de junio',       'number_from' => null, 'number_to' => null, 'price' => 6000],
            ['street_name' => 'tucuman',           'number_from' => 1,    'number_to' => 549,  'price' => 5000],
            ['street_name' => 'tucuman',           'number_from' => 550,  'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'corrientes',        'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'entre rios',        'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'neuquen',           'number_from' => 1,    'number_to' => 1850, 'price' => 5000],
            ['street_name' => 'neuquen',           'number_from' => 1851, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'urquiza',           'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'san luis',          'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'la rioja',          'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'san juan',          'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'catamarca',         'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'jujuy',             'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'salta',             'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'chaco',             'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'formosa',           'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'mendoza',           'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'cordoba',           'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'santiago del estero','number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'italia',            'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'espana',            'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'francia',           'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'lapataia',          'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'diagonal capraro',  'number_from' => null, 'number_to' => null, 'price' => 5000],
            ['street_name' => 'capraro',           'number_from' => null, 'number_to' => null, 'price' => 5000],
            // Costanera (12 de Octubre)
            ['street_name' => 'costanera',         'number_from' => 1,    'number_to' => 2000, 'price' => 5000],
            ['street_name' => '12 de octubre',     'number_from' => 1,    'number_to' => 2000, 'price' => 5000],

            // Calles con rango limitado en 1600 (Límite Almirante Brown al sur, pasando Brown vale 6000)
            ['street_name' => 'onelli',            'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'onelli',            'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'beschtedt',         'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'beschtedt',         'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'beschedt',          'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'beschedt',          'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'rolando',           'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'rolando',           'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'villegas',          'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'villegas',          'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'palacios',          'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'palacios',          'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'quaglia',           'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'quaglia',           'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'morales',           'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'morales',           'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'elordi',            'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'elordi',            'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'ruiz moreno',       'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'ruiz moreno',       'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'sarmiento',         'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'sarmiento',         'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'frey',              'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'frey',              'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'belgrano',          'number_from' => 1,    'number_to' => 399,  'price' => 5000],
            ['street_name' => 'belgrano',          'number_from' => 400,  'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'almirante brown',   'number_from' => 1,    'number_to' => 1800, 'price' => 5000],
            ['street_name' => 'almirante brown',   'number_from' => 1801, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'brown',             'number_from' => 1,    'number_to' => 1800, 'price' => 5000],
            ['street_name' => 'brown',             'number_from' => 1801, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => '9 de julio',        'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => '9 de julio',        'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
 
            // Eduardo Castex (de 1 a 1600 Centro $5000, más arriba $6000)
            ['street_name' => 'castex',            'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'castex',            'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'eduardo castex',    'number_from' => 1,    'number_to' => 1600, 'price' => 5000],
            ['street_name' => 'eduardo castex',    'number_from' => 1601, 'number_to' => 9999, 'price' => 6000],
 
            // Pasaje Gutiérrez (desde Brown hacia arriba vale 6000)
            ['street_name' => 'pasaje gutierrez',  'number_from' => null, 'number_to' => null, 'price' => 6000],
            ['street_name' => 'gutierrez',         'number_from' => null, 'number_to' => null, 'price' => 6000],
 
            // Otras calles de la zona
            ['street_name' => 'albarracin',        'number_from' => 1,    'number_to' => 1500, 'price' => 5000],
            ['street_name' => 'albarracin',        'number_from' => 1800, 'number_to' => 1900, 'price' => 6000],
            ['street_name' => 'perito moreno',     'number_from' => 1,    'number_to' => 1799, 'price' => 5000],
            ['street_name' => 'perito moreno',     'number_from' => 1800, 'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'rivadavia',         'number_from' => 1,    'number_to' => 300,  'price' => 5000],

            // Segundo Sombra
            ['street_name' => 'segundo sombra',    'number_from' => 1,    'number_to' => 1700, 'price' => 5000],
            ['street_name' => 'segundo sombra',    'number_from' => 1701, 'number_to' => 9999, 'price' => 6000],

            // Vuelta de Obligado
            ['street_name' => 'vuelta de obligado','number_from' => 1,    'number_to' => 1799, 'price' => 6000],
            ['street_name' => 'vuelta de obligado','number_from' => 1800, 'number_to' => 2000, 'price' => 5000],
            ['street_name' => 'vuelta de obligado','number_from' => 2001, 'number_to' => 9999, 'price' => 6000],

            // Ñires
            ['street_name' => 'nires',             'number_from' => 1,    'number_to' => 299,  'price' => 5000],
            ['street_name' => 'nires',             'number_from' => 300,  'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'ñires',             'number_from' => 1,    'number_to' => 299,  'price' => 5000],
            ['street_name' => 'ñires',             'number_from' => 300,  'number_to' => 9999, 'price' => 6000],

            // Bailey Willis
            ['street_name' => 'bailey willis',     'number_from' => 1,    'number_to' => 112,  'price' => 5000],
            ['street_name' => 'bailey willis',     'number_from' => 113,  'number_to' => 9999, 'price' => 6000],
            ['street_name' => 'willis',            'number_from' => 1,    'number_to' => 112,  'price' => 5000],
            ['street_name' => 'willis',            'number_from' => 113,  'number_to' => 9999, 'price' => 6000],
        ];
    }
}
