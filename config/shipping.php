<?php

return [
    'zones' => [
        'centro' => [
            'label' => 'Centro',
            'price' => 3000,
        ],
        'belgrano_melipal' => [
            'label' => 'Belgrano / Melipal / 112 Viviendas',
            'price' => 4000,
        ],
        'exterior' => [
            'label' => 'Zona Exterior (Las Victorias, Alun Ruca y alrededores)',
            'price' => 5000,
        ],
    ],

    // Regla de corte por altura: desde Brown hacia arriba.
    // Si una calle de esta lista tiene altura >= from_number,
    // se cobra la zona indicada en to_zone.
    'height_boundary_rule' => [
        'enabled' => true,
        'from_number' => 1700,
        'from_zone' => 'centro',
        'to_zone' => 'belgrano_melipal',
        'streets' => [
            'onelli',
            'oconnor',
            'eduardo oconnor',
            'beschtedt',
            'rolando',
            'mitre',
            'moreno',
            'quaglia',
            'villegas',
            'palacios',
            'libertad',
            'rivadavia',
            'sarmiento',
            'tucuman',
            'gallardo',
            'angel gallardo',
            'elflein',
            'ada maria elflein',
            '20 de febrero',
        ],
    ],
];
