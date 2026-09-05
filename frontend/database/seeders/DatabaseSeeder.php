<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder intencionalmente inactivo.
 *
 * El frontend Laravel es un Backend for Frontend (ADR-0002) y NO habla con la
 * base de datos: todo el acceso a datos pasa por App\Services\ApiClient contra
 * la API Spring Boot. Los usuarios reales se crean en PostgreSQL mediante las
 * migraciones de Flyway del backend (los usuarios semilla, con contraseñas
 * definidas por variables de entorno en V7; en producción se desactivan).
 *
 * Este seeder era un resto de la plantilla de Laravel y crearia una tabla
 * 'users' local con contraseñas en claro versionadas; por eso su cuerpo esta
 * vacio a proposito. Eliminar si no se usa.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Intencionalmente vacio: ver el comentario de clase.
    }
}