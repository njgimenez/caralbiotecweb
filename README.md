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

El checkout usa la integracion Izipay FormToken / Popin basada en Krypton y confirmacion IPN servidor a servidor.

Variables requeridas:

```env
IZIPAY_USERNAME=tu_identificador_de_tienda
IZIPAY_PASSWORD=tu_clave_rest
IZIPAY_PUBLIC_KEY=tu_clave_publica
IZIPAY_HMAC_SHA256=tu_clave_hmac_sha256
IZIPAY_CREATE_PAYMENT_ENDPOINT=https://api.micuentaweb.pe/api-payment/V4/Charge/CreatePayment
IZIPAY_DEFAULT_ZIP_CODE=15000
```

Flujo:

- `/checkout` captura datos del cliente o muestra una orden pendiente recuperable.
- `/checkout/procesar` crea una orden `pending`, copia los items del carrito y redirige a una URL recuperable.
- `/checkout/pago` genera un FormToken para esa orden y renderiza el Popin oficial.
- `/checkout/izipay/resultado` valida la respuesta frontend con `IZIPAY_HMAC_SHA256`.
- `/checkout/izipay/ipn` valida la notificacion servidor a servidor con `IZIPAY_PASSWORD` y confirma el pago.

URL IPN para configurar en el BackOffice Izipay:

```text
https://TU-DOMINIO.com/checkout/izipay/ipn
```

El pago se considera definitivo cuando la IPN confirma `orderStatus = PAID`. El endpoint IPN es idempotente para evitar descuentos duplicados si Izipay reintenta la notificacion.
