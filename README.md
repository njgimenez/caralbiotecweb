# Caral Biotec

E-commerce CMS en PHP para catalogo, carrito, checkout, blog y administracion.

## Requisitos locales

- Docker Desktop o Docker Engine con Compose.
- Puerto `8080` libre para Nginx.
- Puerto `3306` libre para MySQL, o ajustar el puerto publicado en `docker-compose.yml`.

## Levantar en local

1. Copia variables si todavia no existe `.env`:

   ```bash
   cp .env.example .env
   ```

2. Construye y levanta los servicios:

   ```bash
   docker compose up -d --build
   ```

3. Instala dependencias si el volumen local no trae `vendor/`:

   ```bash
   docker compose exec app composer install
   ```

4. Ejecuta migraciones y datos iniciales:

   ```bash
   docker compose exec app composer db:migrate
   docker compose exec app composer db:seed
   ```

5. Abre el sitio:

   - Web publica: http://localhost:8080
   - Admin: http://localhost:8080/admin
   - Login: http://localhost:8080/login

## Usuarios de prueba

Las semillas crean cuentas con contrasena `admin123`:

- `admin@caralbiotec.com`
- `marketing@caralbiotec.com`
- `operador@caralbiotec.com`
- `editor@caralbiotec.com`

## Comandos utiles

```bash
docker compose ps
docker compose logs -f web app db
docker compose exec app composer db:reset
docker compose down
```

Para reiniciar tambien la base de datos local:

```bash
docker compose down -v
```

## Notas

- Docker Compose espera a que MySQL responda antes de iniciar la app PHP.
- `public/install.php` existe como instalador alternativo, pero para desarrollo es preferible usar Phinx con los comandos Composer anteriores.

## Izipay checkout

El checkout usa Izipay Web SDK en modo `pop-up`.

Para desarrollo local queda activo el modo demo:

```env
IZIPAY_ENV=sandbox
IZIPAY_DEMO_MODE=true
```

Con ese modo el popup aprueba una transaccion simulada y permite probar creacion de orden, confirmacion, stock y carrito sin credenciales reales.

Cuando Izipay entregue accesos de sandbox, configura:

```env
IZIPAY_ENV=sandbox
IZIPAY_DEMO_MODE=false
IZIPAY_MERCHANT_CODE=tu_codigo_comercio
IZIPAY_TOKEN_SESSION=token_generado_desde_backend_o_sandbox
IZIPAY_KEY_RSA=tu_llave_publica_rsa
```

La documentacion oficial del popup esta en https://developers.izipay.pe/web-core/modalidades/popup/.
