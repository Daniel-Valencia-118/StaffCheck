# Sistema de Gestión de Personal

Sistema web desarrollado con **Laravel 13**, **React 19** e **Inertia.js 3** para la gestión de personal, fichas y control de acceso.

---

## Características principales

- Registro de personal con datos personales, estudios, experiencia laboral, referencias y contactos.
- Panel administrativo para aprobar/rechazar fichas, gestionar usuarios y visualizar reportes.
- Generación de fichas en PDF con formato oficial.

## El proyecto utiliza:

- Laravel como backend.
- React 19 como frontend.
- Inertia.js para la comunicación entre Laravel y React.
- Vite para la compilación de recursos frontend.
- Tailwind CSS para estilos.
- MySQL como base de datos.
- Apache como servidor web en los despliegues Linux.
- Ziggy para generar las rutas Laravel desde React.

## El proyecto actualmente:

- No utiliza SSR.
- No utiliza Jobs.
- No utiliza Queues.
- No utiliza Notifications.
- No utiliza Emails.
- No utiliza procesos de procesamiento en segundo plano.

---

## Tecnologías utilizadas

- **Backend**: Laravel 13, PHP 8.5+
- **Frontend**: React 19, Inertia.js 3, Tailwind CSS 4, Vite 8.x
- **Base de datos**: MySQL 8+
- **Autenticación**: Laravel Sanctum
- **Composer:** 2.x
- **Node.js:** 22.x recomendado
- **npm:** 10.x
- **PDF**: DomPDF
- **Reportes**: Laravel Excel

---

## Instalación
1. **Clonar el repositorio**
git clone https://github.com/Daniel-Valencia-118/StaffCheck.git

## En Maquina Local

** Instalar Dependencias Composer y Node **
composer install
npm install

** Crear el archivo .env **
cp .env.example .env

** Generar la clave de Laravel **
php artisan key:generate

** Crear Admin, ejecutar:
php artisan make:admin

** Ejecutar las migraciones. **
Una vez configurada la base de datos:
php artisan migrate

** Configuración de almacenamiento **
php artisan storage:link

** Configuración Vite: (opcional)**
El proyecto utiliza una variable de entorno para definir el prefijo público utilizado por Vite.

Por defecto en .env:
VITE_BASE_PATH=/build/

Si la aplicación se publica en el Servidor con un Alias como:
https://dominio/sistema/

se utiliza en .env:
VITE_BASE_PATH=/sistema/build/




## Depliegue Producción
composer install --no-dev --optimize-autoloader
npm ci

Una vez configurado el .env
npm run build


## Configurar el  .htaccess
RewriteBase /registro/