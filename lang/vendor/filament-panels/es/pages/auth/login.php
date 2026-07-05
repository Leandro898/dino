<?php

return [

    'title' => 'Baritienda — Acceso',

    'heading' => 'Ingresá a tu panel',

    'actions' => [

        'register' => [
            'before' => 'o',
            'label' => 'Crear una cuenta',
        ],

        'request_password_reset' => [
            'label' => '¿Olvidaste tu contraseña?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Correo electrónico',
        ],

        'password' => [
            'label' => 'Contraseña',
        ],

        'remember' => [
            'label' => 'Recordarme',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Ingresar',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'El correo o la contraseña no son correctos.',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Demasiados intentos. Intente de nuevo en :seconds segundos.',
            'body' => 'Intente de nuevo en :seconds segundos.',
        ],

    ],

];
