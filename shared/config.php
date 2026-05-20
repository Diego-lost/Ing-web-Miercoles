<?php

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'archivo_central',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'Archivo Central - Carpetas Fiscales',
        'base_url' => '/Practica%20Ing.web/public',
        'timezone' => 'America/Lima',
    ],
    'mail' => [
        'from' => 'archivo.central@fiscalia.gob.pe',
        'from_name' => 'Archivo Central',
        'enabled' => true,
    ],
];
