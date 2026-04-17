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
        // street_name debe estar normalizado: minúsculas, sin tildes, sin prefijos
        // number_from / number_to: null = aplica a toda la calle
        return [

            // ─────────────────────────────────────────────
            // CENTRO — $3.000
            // ─────────────────────────────────────────────
            ['street_name' => 'mitre',             'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'moreno',             'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'quaglia',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'onelli',             'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'o\'connor',          'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'oconnor',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'eduardo oconnor',    'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'beschtedt',          'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'rolando',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'elflein',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'ada maria elflein',  'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'gallardo',           'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'angel gallardo',     'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'tiscornia',          'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'antonio tiscornia',  'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => '20 de febrero',      'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'san martin',         'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'villegas',           'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'palacios',           'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'libertad',           'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'buenos aires',       'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'rivadavia',          'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'sarmiento',          'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'tucuman',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'corrientes',         'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'entre rios',         'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'neuquen',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'urquiza',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'san luis',           'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'la rioja',           'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'san juan',           'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'catamarca',          'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'jujuy',              'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'salta',              'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'chaco',              'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'formosa',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'mendoza',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'cordoba',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'santiago del estero','number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'italia',             'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'espana',             'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'francia',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'lapataia',           'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'diagonal capraro',   'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'capraro',            'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            ['street_name' => 'roca',               'number_from' => null, 'number_to' => null, 'zone_key' => 'centro'],
            // Calles con rango: aplican solo en la parte baja (centro)
            ['street_name' => 'belgrano',           'number_from' => 1,    'number_to' => 699,  'zone_key' => 'centro'],
            ['street_name' => 'albarracin',         'number_from' => 1,    'number_to' => 699,  'zone_key' => 'centro'],
            ['street_name' => 'almirante brown',    'number_from' => 1,    'number_to' => 499,  'zone_key' => 'centro'],
            ['street_name' => 'brown',              'number_from' => 1,    'number_to' => 499,  'zone_key' => 'centro'],
            ['street_name' => 'perito moreno',      'number_from' => 1,    'number_to' => 499,  'zone_key' => 'centro'],
            ['street_name' => '9 de julio',         'number_from' => 1,    'number_to' => 399,  'zone_key' => 'centro'],
            ['street_name' => 'esandi',             'number_from' => 1,    'number_to' => 399,  'zone_key' => 'centro'],

            // ─────────────────────────────────────────────
            // BELGRANO / MELIPAL / 112 VIVIENDAS — $4.000
            // ─────────────────────────────────────────────
            // Tramos medios de calles compartidas con centro
            ['street_name' => 'belgrano',           'number_from' => 700,  'number_to' => 2500, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'albarracin',         'number_from' => 700,  'number_to' => 2500, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'almirante brown',    'number_from' => 500,  'number_to' => 1500, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'brown',              'number_from' => 500,  'number_to' => 1500, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'perito moreno',      'number_from' => 500,  'number_to' => 2000, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => '9 de julio',         'number_from' => 400,  'number_to' => 1500, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'esandi',             'number_from' => 400,  'number_to' => 1200, 'zone_key' => 'belgrano_melipal'],
            // Calles propias de esta zona
            ['street_name' => 'campichuelo',        'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'gutierrez',          'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'diagonal gutierrez', 'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'fragata sarmiento',  'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'malvinas argentinas','number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'malvinas',           'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'los tilos',          'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'los ceibos',         'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'las dalias',         'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'las flores',         'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'lera',               'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'guemes',             'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'alsina',             'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'alvear',             'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'caseros',            'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'paz',                'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'colon',              'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'pilcaniyeu',         'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'melipal',            'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'pioneros',           'number_from' => 1,    'number_to' => 6999, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'los pioneros',       'number_from' => 1,    'number_to' => 6999, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'bustillo',           'number_from' => 1,    'number_to' => 6999, 'zone_key' => 'belgrano_melipal'],
            // 112 Viviendas / San Francisco
            ['street_name' => 'piedra buena',       'number_from' => 1,    'number_to' => 1500, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'luis piedra buena',  'number_from' => 1,    'number_to' => 1500, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'san francisco',      'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],
            ['street_name' => 'bicentenario',       'number_from' => null, 'number_to' => null, 'zone_key' => 'belgrano_melipal'],

            // ─────────────────────────────────────────────
            // ZONA EXTERIOR — $5.000
            // (Las Victorias, Alun Ruca, Omega, Barrio 100 viviendas, etc.)
            // ─────────────────────────────────────────────
            ['street_name' => 'las victorias',      'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'alun ruca',          'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'omega',              'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'nicolas levalle',    'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'levalle',            'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'los radales',        'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'los nires',          'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'los coihues',        'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'los alerces',        'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'rayen cura',         'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'peni mapu',          'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'huiliches',          'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'ruca malen',         'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'el condor',          'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'arrayanes',          'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            ['street_name' => 'arrayana',           'number_from' => null, 'number_to' => null, 'zone_key' => 'exterior'],
            // Tramos exteriores de calles compartidas
            ['street_name' => 'belgrano',           'number_from' => 2501, 'number_to' => 9999, 'zone_key' => 'exterior'],
            ['street_name' => 'albarracin',         'number_from' => 2501, 'number_to' => 9999, 'zone_key' => 'exterior'],
            ['street_name' => 'esandi',             'number_from' => 1201, 'number_to' => 9999, 'zone_key' => 'exterior'],
            ['street_name' => 'pioneros',           'number_from' => 7000, 'number_to' => 99999,'zone_key' => 'exterior'],
            ['street_name' => 'los pioneros',       'number_from' => 7000, 'number_to' => 99999,'zone_key' => 'exterior'],
            ['street_name' => 'bustillo',           'number_from' => 7000, 'number_to' => 99999,'zone_key' => 'exterior'],
            ['street_name' => 'piedra buena',       'number_from' => 1501, 'number_to' => 9999, 'zone_key' => 'exterior'],
            ['street_name' => 'luis piedra buena',  'number_from' => 1501, 'number_to' => 9999, 'zone_key' => 'exterior'],
            ['street_name' => 'perito moreno',      'number_from' => 2001, 'number_to' => 9999, 'zone_key' => 'exterior'],
        ];
    }
}
