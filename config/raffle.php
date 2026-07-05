<?php

return [
    'sales_enabled' => (bool) env('RAFFLE_SALES_ENABLED', false),

    'winner' => [
        'draw' => env('RAFFLE_WINNER_DRAW', 'Nocturna del 09-05'),
        'position' => env('RAFFLE_WINNER_POSITION', 'A la cabeza'),
        'number' => env('RAFFLE_WINNER_NUMBER', '9560'),
        'label' => env('RAFFLE_WINNER_LABEL', 'Virgen'),
    ],
];