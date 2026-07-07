# Despliegue en Banahosting

Esta aplicación puede correr en hosting compartido con Apache/cPanel si el dominio apunta al directorio `public/` o si se sube el proyecto completo usando el `.htaccess` raíz incluido.

## Requisitos

- PHP 8.1.34 o superior.
- Extensiones PHP: `pdo_mysql`, `intl`, `gd`, `zip`, `mbstring`, `json`, `openssl`.
- MySQL 8 o MariaDB compatible.
- Apache con `mod_rewrite` habilitado.
- Permiso de escritura en `public/uploads/`.

## Archivos a subir

Sube el contenido del directorio `caral/` al hosting.

No subas tu `.env` local. En producción crea un `.env` basado en `.env.production.example`.

## Configuración recomendada del dominio

Opción preferida: configurar el document root del dominio o subdominio hacia:

```text
caral/public
```

Opción alternativa: si el document root queda en la carpeta raíz del proyecto, el `.htaccess` raíz redirige todas las solicitudes hacia `public/`.

## Crear base de datos

En cPanel:

1. Crea una base MySQL.
2. Crea un usuario MySQL.
3. Asigna el usuario a la base con todos los privilegios.
4. Copia esos datos en `.env`.

Ejemplo:

```dotenv
APP_ENV=production
APP_KEY=change-this-random-32-char-key

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpaneluser_caralweb
DB_USERNAME=cpaneluser_caraluser
DB_PASSWORD=change-this-password

IZIPAY_ENV=production
IZIPAY_DEMO_MODE=false
IZIPAY_MERCHANT_CODE=change_this
IZIPAY_TOKEN_SESSION=generate_from_backend
IZIPAY_KEY_RSA=change_this
```

## Exportar base local

Desde tu máquina local:

```bash
docker compose -f caral/docker-compose.yml exec db mysqldump -ucaraluser -pcaralpassword caralweb > caral_banahosting.sql
```

Importa `caral_banahosting.sql` en phpMyAdmin de Banahosting.

## Dependencias Composer

Si Banahosting tiene SSH:

```bash
cd /home/USUARIO/ruta/al/proyecto/caral
composer install --no-dev --optimize-autoloader
```

Si no tienes SSH, sube la carpeta `vendor/` generada localmente junto con el proyecto.

## Permisos

La carpeta de subidas debe poder escribir archivos:

```bash
chmod 755 public/uploads
```

En algunos hostings puede requerirse `775`.

## Pruebas después de subir

1. Abrir la tienda: `/`
2. Abrir productos: `/productos`
3. Login admin: `/login`
4. Login POS: `operador@caralbiotec.com`
5. Cargar imagen de producto desde admin.
6. Registrar una venta POS.
7. Imprimir boleta/factura.
8. Abrir reporte POS.

## Cuentas actuales de prueba

Contraseña inicial: `admin123`

- `admin@caralbiotec.com`
- `marketing@caralbiotec.com`
- `operador@caralbiotec.com`

Cambia estas contraseñas antes de usar producción real.

## Notas

- Las rutas amigables dependen de `.htaccess`.
- No elimines `public/.htaccess`.
- No expongas `.env` públicamente.
