<?php

return [
    /*
    |--------------------------------------------------------------------------
    | URL base del backend Spring Boot
    |--------------------------------------------------------------------------
    | Dentro de Docker, los servicios se comunican por nombre de contenedor.
    | Desde el exterior se usa localhost:8080.
    */
    'base_url' => env('API_BASE_URL', 'http://sigcbqr-api:8080/api'),
];
