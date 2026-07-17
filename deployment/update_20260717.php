<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
$dataFile = $root . '/database/data/delivery_routes.php';

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
}

if (!is_file($autoload)) {
    http_response_code(500);
    exit("Falta vendor/autoload.php. No se ejecuto ningun cambio.\n");
}

require_once $autoload;

if (is_file($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

function deployEnv(string $key, string $default = ''): string
{
    $value = $_ENV[$key] ?? getenv($key);

    return $value === false || $value === null ? $default : trim((string)$value);
}

$expectedToken = deployEnv('SCHEMA_UPDATE_TOKEN');
$receivedToken = PHP_SAPI === 'cli'
    ? trim((string)($argv[1] ?? ''))
    : trim((string)($_GET['token'] ?? ''));

if ($expectedToken === '' || !hash_equals($expectedToken, $receivedToken)) {
    http_response_code(403);
    exit("Acceso denegado. Configura SCHEMA_UPDATE_TOKEN y proporciona el token correcto.\n");
}

function columnExists(PDO $pdo, string $database, string $table, string $column): bool
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = :database
          AND TABLE_NAME = :table
          AND COLUMN_NAME = :column
    ');
    $stmt->execute([
        'database' => $database,
        'table' => $table,
        'column' => $column,
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

function indexExists(PDO $pdo, string $database, string $table, string $index): bool
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = :database
          AND TABLE_NAME = :table
          AND INDEX_NAME = :index
    ');
    $stmt->execute([
        'database' => $database,
        'table' => $table,
        'index' => $index,
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

function addColumnIfMissing(
    PDO $pdo,
    string $database,
    string $table,
    string $column,
    string $definition,
    array &$messages
): void {
    if (columnExists($pdo, $database, $table, $column)) {
        $messages[] = "OK: {$table}.{$column} ya existia.";
        return;
    }

    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    $messages[] = "CREADO: {$table}.{$column}.";
}

function activeIzipayMode(PDO $pdo): string
{
    $mode = strtolower(deployEnv('IZIPAY_MODE', 'test'));

    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'company_settings' LIMIT 1");
        $stmt->execute();
        $settings = json_decode((string)$stmt->fetchColumn(), true);
        if (is_array($settings) && isset($settings['izipay_mode'])) {
            $mode = strtolower(trim((string)$settings['izipay_mode']));
        }
    } catch (Throwable $exception) {
        // Conserva el valor del entorno si la configuracion aun no existe.
    }

    return in_array($mode, ['production', 'prod', 'live'], true) ? 'production' : 'test';
}

function seedDelivery(PDO $pdo, string $dataFile, array &$messages): void
{
    if (!is_file($dataFile)) {
        throw new RuntimeException('No se encontro database/data/delivery_routes.php.');
    }

    $data = require $dataFile;
    $routes = is_array($data['routes'] ?? null) ? $data['routes'] : [];
    $districts = is_array($data['districts'] ?? null) ? $data['districts'] : [];

    $routeStmt = $pdo->prepare('
        INSERT INTO delivery_rutas
            (codigo, nombre, zona_cardinal, descripcion, tarifa, tiempo_min, tiempo_max,
             color, icono, hora_inicio, hora_fin, orden, activo)
        VALUES
            (:codigo, :nombre, :zona_cardinal, :descripcion, :tarifa, :tiempo_min, :tiempo_max,
             :color, :icono, :hora_inicio, :hora_fin, :orden, 1)
        ON DUPLICATE KEY UPDATE
            nombre = VALUES(nombre),
            zona_cardinal = VALUES(zona_cardinal),
            descripcion = VALUES(descripcion),
            tarifa = VALUES(tarifa),
            tiempo_min = VALUES(tiempo_min),
            tiempo_max = VALUES(tiempo_max),
            color = VALUES(color),
            icono = VALUES(icono),
            hora_inicio = VALUES(hora_inicio),
            hora_fin = VALUES(hora_fin),
            orden = VALUES(orden),
            activo = 1
    ');

    foreach ($routes as $route) {
        $routeStmt->execute([
            'codigo' => $route['codigo'],
            'nombre' => $route['nombre'],
            'zona_cardinal' => $route['zona_cardinal'],
            'descripcion' => $route['descripcion'],
            'tarifa' => $route['tarifa'],
            'tiempo_min' => $route['tiempo_min'],
            'tiempo_max' => $route['tiempo_max'],
            'color' => $route['color'],
            'icono' => $route['icono'],
            'hora_inicio' => $route['hora_inicio'],
            'hora_fin' => $route['hora_fin'],
            'orden' => $route['orden'],
        ]);
    }

    $districtStmt = $pdo->prepare('
        INSERT INTO delivery_distritos
            (ubigeo, departamento, provincia, distrito, zona_cardinal, ruta_delivery, orden_ruta, activo)
        VALUES
            (:ubigeo, :departamento, :provincia, :distrito, :zona_cardinal, :ruta_delivery, :orden_ruta, 1)
        ON DUPLICATE KEY UPDATE
            ubigeo = VALUES(ubigeo),
            departamento = VALUES(departamento),
            zona_cardinal = VALUES(zona_cardinal),
            ruta_delivery = VALUES(ruta_delivery),
            orden_ruta = VALUES(orden_ruta),
            activo = 1
    ');

    foreach ($districts as $district) {
        $districtStmt->execute([
            'ubigeo' => $district['ubigeo'],
            'departamento' => $district['departamento'],
            'provincia' => $district['provincia'],
            'distrito' => $district['distrito'],
            'zona_cardinal' => $district['zona_cardinal'],
            'ruta_delivery' => $district['ruta_delivery'],
            'orden_ruta' => $district['orden_ruta'],
        ]);
    }

    $messages[] = 'DATOS: ' . count($routes) . ' rutas y ' . count($districts) . ' distritos actualizados.';
}

function ensureCmsBlocks(PDO $pdo, array &$messages): void
{
    $blocks = [
        ['home_hero', 'Seccion Hero Principal', 1],
        ['home_benefits', 'Barra de Beneficios (Trust Badges)', 1],
        ['home_cta', 'Llamado a la Accion Personalizado (CTA)', 1],
        ['home_brand', 'Marca y recursos visuales', 1],
        ['home_categories', 'Categorias del Home', 0],
        ['home_needs', 'Compra segun tu necesidad', 0],
        ['home_featured_products', 'Productos destacados', 1],
        ['home_blog', 'Ultimas entradas del blog', 1],
    ];

    $stmt = $pdo->prepare('
        INSERT INTO cms_blocks (block_key, title, content_json, is_active)
        VALUES (:block_key, :title, :content_json, :is_active)
        ON DUPLICATE KEY UPDATE block_key = VALUES(block_key)
    ');

    foreach ($blocks as [$key, $title, $active]) {
        $stmt->execute([
            'block_key' => $key,
            'title' => $title,
            'content_json' => '{}',
            'is_active' => $active,
        ]);
    }

    $messages[] = 'CMS: bloques faltantes creados; contenido y estados existentes preservados.';
}

try {
    @set_time_limit(0);

    $database = deployEnv('DB_DATABASE');
    if ($database === '') {
        throw new RuntimeException('DB_DATABASE no esta configurado.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        deployEnv('DB_HOST', 'localhost'),
        deployEnv('DB_PORT', '3306'),
        $database
    );

    $pdo = new PDO($dsn, deployEnv('DB_USERNAME'), deployEnv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $messages = [];

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS delivery_rutas (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(20) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            zona_cardinal VARCHAR(20) NOT NULL,
            descripcion TEXT NULL,
            tarifa DECIMAL(10,2) NOT NULL,
            tiempo_min SMALLINT UNSIGNED NOT NULL,
            tiempo_max SMALLINT UNSIGNED NOT NULL,
            color VARCHAR(20) NULL,
            icono VARCHAR(50) NULL,
            hora_inicio TIME NULL,
            hora_fin TIME NULL,
            orden SMALLINT UNSIGNED NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_delivery_rutas_codigo (codigo),
            KEY idx_delivery_rutas_activo_orden (activo, orden)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ');
    $messages[] = 'OK: tabla delivery_rutas.';

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS delivery_distritos (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ubigeo CHAR(6) NOT NULL,
            departamento VARCHAR(50) NOT NULL,
            provincia VARCHAR(50) NOT NULL,
            distrito VARCHAR(80) NOT NULL,
            zona_cardinal VARCHAR(20) NOT NULL,
            ruta_delivery VARCHAR(20) NOT NULL,
            orden_ruta SMALLINT UNSIGNED DEFAULT 1,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_delivery_distrito (provincia, distrito),
            KEY idx_delivery_distritos_ubigeo (ubigeo),
            KEY idx_delivery_distritos_distrito (distrito),
            KEY idx_delivery_distritos_ruta (ruta_delivery),
            KEY idx_delivery_distritos_zona (zona_cardinal)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ');
    $messages[] = 'OK: tabla delivery_distritos.';

    addColumnIfMissing($pdo, $database, 'orders', 'fulfillment_method', "VARCHAR(20) NOT NULL DEFAULT 'delivery' AFTER shipping_city", $messages);
    addColumnIfMissing($pdo, $database, 'orders', 'delivery_zone', 'VARCHAR(30) NULL AFTER shipping_city', $messages);
    addColumnIfMissing($pdo, $database, 'orders', 'delivery_route_code', 'VARCHAR(20) NULL AFTER delivery_zone', $messages);
    addColumnIfMissing($pdo, $database, 'orders', 'delivery_route_name', 'VARCHAR(100) NULL AFTER delivery_route_code', $messages);
    addColumnIfMissing($pdo, $database, 'orders', 'delivery_time_min', 'SMALLINT UNSIGNED NULL AFTER delivery_route_name', $messages);
    addColumnIfMissing($pdo, $database, 'orders', 'delivery_time_max', 'SMALLINT UNSIGNED NULL AFTER delivery_time_min', $messages);
    addColumnIfMissing($pdo, $database, 'orders', 'payment_environment', "VARCHAR(20) NOT NULL DEFAULT 'test' AFTER payment_provider", $messages);
    addColumnIfMissing($pdo, $database, 'orders', 'delivery_type', "VARCHAR(20) NOT NULL DEFAULT 'lima' AFTER fulfillment_method", $messages);
    addColumnIfMissing($pdo, $database, 'orders', 'courier', 'VARCHAR(30) NULL AFTER delivery_type', $messages);

    if (!indexExists($pdo, $database, 'orders', 'idx_orders_payment_environment')) {
        $pdo->exec('ALTER TABLE orders ADD INDEX idx_orders_payment_environment (payment_environment)');
        $messages[] = 'CREADO: indice idx_orders_payment_environment.';
    } else {
        $messages[] = 'OK: indice idx_orders_payment_environment ya existia.';
    }

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS product_media (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id BIGINT UNSIGNED NOT NULL,
            media_type VARCHAR(20) NOT NULL DEFAULT "image",
            url VARCHAR(600) NOT NULL,
            title VARCHAR(190) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_product_media_product (product_id),
            CONSTRAINT fk_product_media_product
                FOREIGN KEY (product_id) REFERENCES products(id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ');
    $messages[] = 'OK: tabla product_media.';

    $insertedMedia = $pdo->exec('
        INSERT INTO product_media (product_id, media_type, url, title, sort_order)
        SELECT p.id, "image", p.image_url, "Imagen principal", 0
        FROM products p
        WHERE p.image_url IS NOT NULL
          AND p.image_url <> ""
          AND NOT EXISTS (
              SELECT 1 FROM product_media pm WHERE pm.product_id = p.id
          )
    ');
    $messages[] = 'GALERIA: ' . (int)$insertedMedia . ' imagenes principales migradas.';

    seedDelivery($pdo, $dataFile, $messages);

    $mode = activeIzipayMode($pdo);
    $paymentStmt = $pdo->prepare("
        UPDATE orders
        SET payment_environment = :mode
        WHERE payment_environment IS NULL OR payment_environment = ''
    ");
    $paymentStmt->execute(['mode' => $mode]);

    $pdo->exec("
        UPDATE orders
        SET delivery_type = CASE
            WHEN fulfillment_method = 'pickup' THEN 'pickup'
            WHEN shipping_city IS NOT NULL
             AND LOWER(shipping_city) NOT IN ('lima', 'lima metropolitana', 'callao') THEN 'province'
            ELSE 'lima'
        END
        WHERE delivery_type IS NULL OR delivery_type = ''
    ");
    $messages[] = "ORDENES: datos anteriores normalizados para ambiente {$mode}.";

    ensureCmsBlocks($pdo, $messages);

    echo "ACTUALIZACION COMPLETADA\n";
    echo "=======================\n";
    echo implode("\n", $messages) . "\n\n";
    echo "Elimina public/update_20260717.php del servidor ahora.\n";
} catch (Throwable $exception) {
    http_response_code(500);
    echo "ERROR: {$exception->getMessage()}\n";
    echo "El script es idempotente: corrige el problema y vuelve a ejecutarlo.\n";
    exit(1);
}
