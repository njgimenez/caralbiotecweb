<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$lockFile = $root . '/.install.lock';
$autoload = $root . '/vendor/autoload.php';

if (file_exists($lockFile)) {
    http_response_code(403);
    echo '<h1>Instalador deshabilitado</h1><p>La instalacion ya fue completada. Elimina <code>.install.lock</code> si necesitas volver a ejecutarlo.</p>';
    exit;
}

if (!file_exists($autoload)) {
    http_response_code(500);
    echo '<h1>Falta vendor/autoload.php</h1><p>Sube primero las dependencias Composer.</p>';
    exit;
}

require $autoload;

if (file_exists($root . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($root);
    $dotenv->load();
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function env_value(string $key, string $default = ''): string
{
    return (string)($_ENV[$key] ?? $_POST[$key] ?? $default);
}

function pdo_connect(array $data): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%s;charset=utf8mb4',
        $data['DB_HOST'],
        $data['DB_PORT']
    );

    return new PDO($dsn, $data['DB_USERNAME'], $data['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function exec_sql(PDO $pdo, string $sql): void
{
    $pdo->exec($sql);
}

function insert_ignore(PDO $pdo, string $table, array $row): void
{
    $columns = array_keys($row);
    $sql = sprintf(
        'INSERT IGNORE INTO `%s` (%s) VALUES (%s)',
        $table,
        implode(', ', array_map(fn($col) => "`$col`", $columns)),
        implode(', ', array_fill(0, count($columns), '?'))
    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($row));
}

function upsert(PDO $pdo, string $table, array $row, array $updateColumns, string $uniqueKey): void
{
    $columns = array_keys($row);
    $sql = sprintf(
        'INSERT INTO `%s` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
        $table,
        implode(', ', array_map(fn($col) => "`$col`", $columns)),
        implode(', ', array_fill(0, count($columns), '?')),
        implode(', ', array_map(fn($col) => "`$col` = VALUES(`$col`)", $updateColumns))
    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($row));
}

$defaults = [
    'APP_ENV' => env_value('APP_ENV', 'production'),
    'APP_KEY' => env_value('APP_KEY', ''),
    'DB_HOST' => env_value('DB_HOST', 'localhost'),
    'DB_PORT' => env_value('DB_PORT', '3306'),
    'DB_DATABASE' => env_value('DB_DATABASE', ''),
    'DB_USERNAME' => env_value('DB_USERNAME', ''),
    'DB_PASSWORD' => env_value('DB_PASSWORD', ''),
];

$messages = [];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $config = [
            'DB_HOST' => trim((string)($_POST['DB_HOST'] ?? $defaults['DB_HOST'])),
            'DB_PORT' => trim((string)($_POST['DB_PORT'] ?? $defaults['DB_PORT'])),
            'DB_DATABASE' => trim((string)($_POST['DB_DATABASE'] ?? $defaults['DB_DATABASE'])),
            'DB_USERNAME' => trim((string)($_POST['DB_USERNAME'] ?? $defaults['DB_USERNAME'])),
            'DB_PASSWORD' => (string)($_POST['DB_PASSWORD'] ?? $defaults['DB_PASSWORD']),
        ];

        if ($config['DB_DATABASE'] === '' || $config['DB_USERNAME'] === '') {
            throw new RuntimeException('Completa la base de datos y el usuario.');
        }

        $pdo = pdo_connect($config);
        $pdo->exec(sprintf('USE `%s`', str_replace('`', '``', $config['DB_DATABASE'])));

        $messages[] = 'Conexi&oacute;n lista.';

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS phinxlog (
                version BIGINT(20) NOT NULL,
                migration_name VARCHAR(100) NULL,
                start_time TIMESTAMP NULL,
                end_time TIMESTAMP NULL,
                breakpoint TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (version)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(190) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_users_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS roles (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                description VARCHAR(255) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_roles_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS permissions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                description VARCHAR(255) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_permissions_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS user_roles (
                user_id BIGINT UNSIGNED NOT NULL,
                role_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (user_id, role_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS role_permissions (
                role_id BIGINT UNSIGNED NOT NULL,
                permission_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (role_id, permission_id),
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS categories (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL,
                description TEXT NULL,
                image_url VARCHAR(255) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_categories_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS health_conditions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL,
                description TEXT NULL,
                icon VARCHAR(50) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_conditions_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS products (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(200) NOT NULL,
                slug VARCHAR(200) NOT NULL,
                sku VARCHAR(50) NOT NULL,
                short_description TEXT NULL,
                description TEXT NULL,
                price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                stock INT NOT NULL DEFAULT 0,
                image_url VARCHAR(255) NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_products_slug (slug),
                UNIQUE KEY uq_products_sku (sku),
                KEY idx_products_category (category_id),
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS product_conditions (
                product_id BIGINT UNSIGNED NOT NULL,
                health_condition_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (product_id, health_condition_id),
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (health_condition_id) REFERENCES health_conditions(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS shopping_carts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                public_token CHAR(36) NOT NULL,
                user_id BIGINT UNSIGNED NULL,
                session_token_hash CHAR(64) NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                currency CHAR(3) NOT NULL DEFAULT 'PEN',
                last_activity_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_carts_token (public_token),
                KEY idx_carts_session (session_token_hash),
                KEY idx_carts_user_status (user_id, status),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS cart_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                shopping_cart_id BIGINT UNSIGNED NOT NULL,
                product_id BIGINT UNSIGNED NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                price_seen DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_cart_product (shopping_cart_id, product_id),
                FOREIGN KEY (shopping_cart_id) REFERENCES shopping_carts(id) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS audit_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                action VARCHAR(100) NOT NULL,
                entity VARCHAR(50) NULL,
                entity_id BIGINT UNSIGNED NULL,
                description TEXT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent VARCHAR(255) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_audit_user (user_id),
                KEY idx_audit_action (action),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS cms_blocks (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                block_key VARCHAR(50) NOT NULL,
                title VARCHAR(150) NOT NULL,
                content_json LONGTEXT NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_cms_blocks_key (block_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS orders (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(30) NOT NULL,
                user_id BIGINT UNSIGNED NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'pending',
                customer_name VARCHAR(150) NOT NULL,
                customer_email VARCHAR(254) NOT NULL,
                customer_phone VARCHAR(30) NULL,
                shipping_address TEXT NULL,
                shipping_district VARCHAR(100) NULL,
                shipping_city VARCHAR(100) NULL,
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                currency CHAR(3) NOT NULL DEFAULT 'PEN',
                document_type VARCHAR(20) NULL,
                document_number VARCHAR(20) NULL,
                document_name VARCHAR(180) NULL,
                payment_method VARCHAR(50) NULL,
                payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
                payment_operation_id VARCHAR(100) NULL,
                payment_response_json LONGTEXT NULL,
                notes TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_orders_number (order_number),
                INDEX idx_orders_user (user_id),
                INDEX idx_orders_status (status),
                INDEX idx_orders_email (customer_email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS order_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id BIGINT UNSIGNED NOT NULL,
                product_id BIGINT UNSIGNED NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                product_sku VARCHAR(80) NOT NULL,
                quantity INT UNSIGNED NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                total_price DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS settings (
                setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
                setting_value LONGTEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        exec_sql($pdo, "
            CREATE TABLE IF NOT EXISTS blog_posts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                author_id BIGINT UNSIGNED NULL,
                title VARCHAR(220) NOT NULL,
                slug VARCHAR(220) NOT NULL,
                excerpt VARCHAR(320) NULL,
                content_html MEDIUMTEXT NOT NULL,
                featured_image_url VARCHAR(500) NULL,
                meta_title VARCHAR(220) NULL,
                meta_description VARCHAR(320) NULL,
                tags VARCHAR(500) NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'draft',
                published_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_blog_posts_slug (slug),
                KEY idx_blog_posts_status_published (status, published_at),
                KEY idx_blog_posts_author (author_id),
                FULLTEXT KEY ft_blog_posts_search (title, excerpt, content_html, tags),
                FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $hash = password_hash('admin123', PASSWORD_BCRYPT);

        insert_ignore($pdo, 'roles', ['id' => 1, 'name' => 'Super Administrador', 'description' => 'Acceso total']);
        insert_ignore($pdo, 'roles', ['id' => 2, 'name' => 'Marketing', 'description' => 'Gestión de contenidos y promociones']);
        insert_ignore($pdo, 'roles', ['id' => 3, 'name' => 'Operaciones', 'description' => 'Gestión de pedidos y despachos']);
        insert_ignore($pdo, 'roles', ['id' => 4, 'name' => 'Cliente', 'description' => 'Cliente registrado']);
        insert_ignore($pdo, 'roles', ['id' => 5, 'name' => 'Editor', 'description' => 'Gestión editorial del blog']);

        insert_ignore($pdo, 'permissions', ['id' => 1, 'name' => 'manage_users', 'description' => 'Administrar usuarios del sistema']);
        insert_ignore($pdo, 'permissions', ['id' => 2, 'name' => 'manage_cms', 'description' => 'Administrar páginas y bloques del CMS']);
        insert_ignore($pdo, 'permissions', ['id' => 3, 'name' => 'manage_catalog', 'description' => 'Administrar productos y categorías']);
        insert_ignore($pdo, 'permissions', ['id' => 4, 'name' => 'view_dashboard', 'description' => 'Ver indicadores del panel de control']);
        insert_ignore($pdo, 'permissions', ['id' => 5, 'name' => 'manage_blog', 'description' => 'Administrar entradas del blog']);

        insert_ignore($pdo, 'role_permissions', ['role_id' => 1, 'permission_id' => 1]);
        insert_ignore($pdo, 'role_permissions', ['role_id' => 1, 'permission_id' => 2]);
        insert_ignore($pdo, 'role_permissions', ['role_id' => 1, 'permission_id' => 3]);
        insert_ignore($pdo, 'role_permissions', ['role_id' => 1, 'permission_id' => 4]);
        insert_ignore($pdo, 'role_permissions', ['role_id' => 1, 'permission_id' => 5]);
        insert_ignore($pdo, 'role_permissions', ['role_id' => 2, 'permission_id' => 2]);
        insert_ignore($pdo, 'role_permissions', ['role_id' => 2, 'permission_id' => 5]);
        insert_ignore($pdo, 'role_permissions', ['role_id' => 5, 'permission_id' => 5]);

        upsert($pdo, 'users', ['id' => 1, 'email' => 'admin@caralbiotec.com', 'password_hash' => $hash, 'status' => 'active'], ['email', 'password_hash', 'status'], 'uq_users_email');
        upsert($pdo, 'users', ['id' => 2, 'email' => 'marketing@caralbiotec.com', 'password_hash' => $hash, 'status' => 'active'], ['email', 'password_hash', 'status'], 'uq_users_email');
        upsert($pdo, 'users', ['id' => 3, 'email' => 'operador@caralbiotec.com', 'password_hash' => $hash, 'status' => 'active'], ['email', 'password_hash', 'status'], 'uq_users_email');
        upsert($pdo, 'users', ['id' => 4, 'email' => 'editor@caralbiotec.com', 'password_hash' => $hash, 'status' => 'active'], ['email', 'password_hash', 'status'], 'uq_users_email');

        insert_ignore($pdo, 'user_roles', ['user_id' => 1, 'role_id' => 1]);
        insert_ignore($pdo, 'user_roles', ['user_id' => 2, 'role_id' => 2]);
        insert_ignore($pdo, 'user_roles', ['user_id' => 3, 'role_id' => 4]);
        insert_ignore($pdo, 'user_roles', ['user_id' => 4, 'role_id' => 5]);

        insert_ignore($pdo, 'categories', ['id' => 1, 'name' => 'Nutracéuticos', 'slug' => 'nutraceuticos', 'description' => 'Suplementos para tu salud y nutrición', 'image_url' => '/uploads/cat_nutraceuticos.png']);
        insert_ignore($pdo, 'categories', ['id' => 2, 'name' => 'Bienestar', 'slug' => 'bienestar', 'description' => 'Descanso, confort y cuidado personal', 'image_url' => '/uploads/cat_bienestar.png']);
        insert_ignore($pdo, 'categories', ['id' => 3, 'name' => 'Rehabilitación', 'slug' => 'rehabilitacion', 'description' => 'Equipos y accesorios para tu recuperación', 'image_url' => '/uploads/cat_rehabilitacion.png']);
        insert_ignore($pdo, 'categories', ['id' => 4, 'name' => 'Apoyo al Paciente', 'slug' => 'apoyo-al-paciente', 'description' => 'Productos de apoyo para tu día a día', 'image_url' => '/uploads/cat_apoyo.png']);

        insert_ignore($pdo, 'health_conditions', ['id' => 1, 'name' => 'Cáncer', 'slug' => 'cancer', 'description' => 'Productos para el bienestar y cuidado integral', 'icon' => 'ribbon']);
        insert_ignore($pdo, 'health_conditions', ['id' => 2, 'name' => 'Diabetes', 'slug' => 'diabetes', 'description' => 'Control, prevención y bienestar diario', 'icon' => 'droplet']);
        insert_ignore($pdo, 'health_conditions', ['id' => 3, 'name' => 'Osteoporosis', 'slug' => 'osteoporosis', 'description' => 'Fortalece tus huesos y mejora tu calidad de vida', 'icon' => 'bone']);
        insert_ignore($pdo, 'health_conditions', ['id' => 4, 'name' => 'Cardiológicos', 'slug' => 'cardiologicos', 'description' => 'Cuida tu corazón y mejora tu salud', 'icon' => 'heart']);
        insert_ignore($pdo, 'health_conditions', ['id' => 5, 'name' => 'Adulto Mayor', 'slug' => 'adulto-mayor', 'description' => 'Bienestar, seguridad y confort diario', 'icon' => 'person']);
        insert_ignore($pdo, 'health_conditions', ['id' => 6, 'name' => 'Rehabilitación', 'slug' => 'rehabilitacion-condicion', 'description' => 'Recupera tu movilidad y bienestar', 'icon' => 'activity']);

        insert_ignore($pdo, 'products', ['id' => 1, 'category_id' => 1, 'name' => 'Colágeno Hidrolizado Premium', 'slug' => 'colageno-hidrolizado-premium', 'sku' => 'NUT-COL-001', 'short_description' => 'Colágeno de alta absorción para fortalecer articulaciones y piel.', 'description' => 'Nuestro Colágeno Hidrolizado Premium está enriquecido con Vitamina C y Magnesio, diseñado especialmente para mejorar la elasticidad de la piel, fortalecer el cabello y brindar soporte estructural a tus articulaciones y huesos.', 'price' => 89.90, 'stock' => 50, 'image_url' => '/uploads/colageno.jpg', 'is_active' => 1]);
        insert_ignore($pdo, 'products', ['id' => 2, 'category_id' => 3, 'name' => 'Pistola de Masaje Vacufast', 'slug' => 'pistola-masaje-vacufast', 'sku' => 'REH-VAC-001', 'short_description' => 'Dispositivo de percusión profesional para aliviar dolores musculares.', 'description' => 'La pistola de masaje Vacufast cuenta con 6 cabezales intercambiables y 20 niveles de velocidad. Ideal para la rehabilitación muscular.', 'price' => 250.00, 'stock' => 15, 'image_url' => '/uploads/vacufast.jpg', 'is_active' => 1]);
        insert_ignore($pdo, 'products', ['id' => 3, 'category_id' => 2, 'name' => 'Almohada Ergonómica Confort', 'slug' => 'almohada-ergonomica-confort', 'sku' => 'BIE-ALM-001', 'short_description' => 'Almohada de espuma viscoelástica para soporte cervical.', 'description' => 'Diseñada anatómicamente para mantener alineada la columna cervical durante el descanso. Memory foam de alta densidad.', 'price' => 120.00, 'stock' => 30, 'image_url' => '/uploads/almohada.jpg', 'is_active' => 1]);
        insert_ignore($pdo, 'products', ['id' => 4, 'category_id' => 4, 'name' => 'Bastón Regulable de Aluminio', 'slug' => 'baston-regulable-aluminio', 'sku' => 'APO-BAS-001', 'short_description' => 'Bastón ligero de aluminio con mango ergonómico.', 'description' => 'Bastón de apoyo regulable en altura con base antideslizante. Aluminio anodizado de alta resistencia.', 'price' => 65.00, 'stock' => 40, 'image_url' => '/uploads/baston.jpg', 'is_active' => 1]);
        insert_ignore($pdo, 'products', ['id' => 5, 'category_id' => 1, 'name' => 'Multivitamínico Oncológico Care', 'slug' => 'multivitaminico-oncologico-care', 'sku' => 'NUT-ONC-001', 'short_description' => 'Suplemento nutricional de apoyo integral para el sistema inmunológico.', 'description' => 'Fórmula especial rica en antioxidantes, vitaminas y minerales clave para dar soporte nutricional y mantener las defensas activas.', 'price' => 110.00, 'stock' => 25, 'image_url' => '/uploads/multivitaminico.jpg', 'is_active' => 1]);

        insert_ignore($pdo, 'product_conditions', ['product_id' => 1, 'health_condition_id' => 3]);
        insert_ignore($pdo, 'product_conditions', ['product_id' => 2, 'health_condition_id' => 6]);
        insert_ignore($pdo, 'product_conditions', ['product_id' => 4, 'health_condition_id' => 5]);
        insert_ignore($pdo, 'product_conditions', ['product_id' => 4, 'health_condition_id' => 6]);
        insert_ignore($pdo, 'product_conditions', ['product_id' => 5, 'health_condition_id' => 1]);

        $heroData = [
            'tag_text' => 'Productos certificados · Lima, Perú',
            'title_part1' => 'Soluciones integrales para tu',
            'title_accent' => 'bienestar',
            'title_part2' => 'y recuperación',
            'subtitle' => 'Nutracéuticos, equipos de rehabilitación y productos de bienestar seleccionados por especialistas para mejorar tu calidad de vida.',
            'btn_primary_text' => 'Comprar ahora',
            'btn_primary_url' => '/productos',
            'btn_secondary_text' => 'Ver categorías',
            'btn_secondary_url' => '#categorias',
        ];

        $benefitsData = [
            ['icon' => 'truck', 'title' => 'Envíos a todo el Perú', 'desc' => 'Rápidos y seguros'],
            ['icon' => 'shield-check', 'title' => 'Productos de calidad', 'desc' => 'Garantía y respaldo'],
            ['icon' => 'lock', 'title' => 'Compra 100% segura', 'desc' => 'Tus datos protegidos'],
            ['icon' => 'headset', 'title' => 'Atención personalizada', 'desc' => 'Te asesoramos siempre'],
        ];

        $ctaData = [
            'title' => '¿Necesitas asesoría personalizada?',
            'subtitle' => 'Nuestro equipo de especialistas está listo para ayudarte a elegir el producto ideal.',
            'btn_text' => 'Chatear por WhatsApp',
            'btn_url' => 'https://wa.me/51947123456',
            'btn_icon' => 'whatsapp',
        ];

        upsert($pdo, 'cms_blocks', ['block_key' => 'home_hero', 'title' => 'Sección Hero Principal', 'content_json' => json_encode($heroData, JSON_UNESCAPED_UNICODE), 'is_active' => 1], ['title', 'content_json', 'is_active'], 'uq_cms_blocks_key');
        upsert($pdo, 'cms_blocks', ['block_key' => 'home_benefits', 'title' => 'Barra de Beneficios (Trust Badges)', 'content_json' => json_encode($benefitsData, JSON_UNESCAPED_UNICODE), 'is_active' => 1], ['title', 'content_json', 'is_active'], 'uq_cms_blocks_key');
        upsert($pdo, 'cms_blocks', ['block_key' => 'home_cta', 'title' => 'Llamado a la Acción Personalizado (CTA)', 'content_json' => json_encode($ctaData, JSON_UNESCAPED_UNICODE), 'is_active' => 1], ['title', 'content_json', 'is_active'], 'uq_cms_blocks_key');

        $companySettings = [
            'business_name' => 'Caral Biotec',
            'trade_name' => 'Caral Biotec',
            'ruc' => '',
            'address' => 'Lima, Perú',
            'phone' => '',
            'email' => 'contacto@caralbiotec.com',
            'igv_percent' => 18.00,
        ];
        upsert($pdo, 'settings', ['setting_key' => 'company_settings', 'setting_value' => json_encode($companySettings, JSON_UNESCAPED_UNICODE)], ['setting_value'], 'PRIMARY');

        $blogContent = '<h2>Bienestar sostenible</h2><p>Una rutina simple y constante funciona mejor que un plan complicado.</p><p>https://www.youtube.com/watch?v=dQw4w9WgXcQ</p><p>https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1200&q=80</p>';
        upsert($pdo, 'blog_posts', [
            'author_id' => 4,
            'title' => 'Guia para iniciar una rutina de bienestar sostenible',
            'slug' => 'guia-rutina-bienestar-sostenible',
            'excerpt' => 'Aprende como organizar una rutina simple para cuidar tu bienestar diario con habitos sostenibles y apoyo especializado.',
            'content_html' => $blogContent,
            'featured_image_url' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1200&q=80',
            'meta_title' => 'Guia para una rutina de bienestar sostenible',
            'meta_description' => 'Consejos practicos para crear una rutina de bienestar simple, sostenible y facil de seguir.',
            'tags' => 'bienestar, salud, rutina',
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
        ], ['author_id', 'title', 'excerpt', 'content_html', 'featured_image_url', 'meta_title', 'meta_description', 'tags', 'status', 'published_at'], 'uq_blog_posts_slug');

        insert_ignore($pdo, 'phinxlog', ['version' => 20260629000000, 'migration_name' => 'CreateBaseTables', 'start_time' => date('Y-m-d H:i:s'), 'end_time' => date('Y-m-d H:i:s'), 'breakpoint' => 0]);
        insert_ignore($pdo, 'phinxlog', ['version' => 20260629000001, 'migration_name' => 'CreateCmsBlocks', 'start_time' => date('Y-m-d H:i:s'), 'end_time' => date('Y-m-d H:i:s'), 'breakpoint' => 0]);
        insert_ignore($pdo, 'phinxlog', ['version' => 20260629000002, 'migration_name' => 'CreateOrders', 'start_time' => date('Y-m-d H:i:s'), 'end_time' => date('Y-m-d H:i:s'), 'breakpoint' => 0]);
        insert_ignore($pdo, 'phinxlog', ['version' => 20260630000003, 'migration_name' => 'AddPosDocumentFieldsToOrders', 'start_time' => date('Y-m-d H:i:s'), 'end_time' => date('Y-m-d H:i:s'), 'breakpoint' => 0]);
        insert_ignore($pdo, 'phinxlog', ['version' => 20260630000004, 'migration_name' => 'CreateSettingsTable', 'start_time' => date('Y-m-d H:i:s'), 'end_time' => date('Y-m-d H:i:s'), 'breakpoint' => 0]);
        insert_ignore($pdo, 'phinxlog', ['version' => 20260630000005, 'migration_name' => 'CreateBlogPostsTable', 'start_time' => date('Y-m-d H:i:s'), 'end_time' => date('Y-m-d H:i:s'), 'breakpoint' => 0]);

        @file_put_contents($lockFile, 'Installed at ' . date('c'));
        $done = true;
        $messages[] = 'Instalacion completada.';
    } catch (Throwable $e) {
        $messages[] = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Caral Biotec</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f3f5f7; color:#111827; margin:0; padding:40px 16px; }
        .wrap { max-width:760px; margin:0 auto; background:#fff; border:1px solid #dbe3ec; border-radius:12px; padding:24px; box-shadow:0 12px 30px rgba(15,23,42,.08); }
        h1 { margin:0 0 8px; font-size:24px; }
        p { line-height:1.55; }
        label { display:block; font-size:14px; font-weight:700; margin:14px 0 6px; }
        input { width:100%; box-sizing:border-box; padding:12px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; }
        button { margin-top:18px; background:#4b2bb0; color:#fff; border:0; padding:12px 18px; border-radius:8px; font-weight:700; cursor:pointer; }
        button:hover { opacity:.92; }
        .msg { padding:12px 14px; border-radius:8px; margin:12px 0; background:#f8fafc; border:1px solid #e2e8f0; }
        .ok { background:#f6f3ff; border-color:#d4ccff; color:#28135d; }
        .err { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .small { font-size:12px; color:#64748b; }
        @media (max-width: 640px) { .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Instalador de Caral Biotec</h1>
    <p>Este script crea las tablas, datos iniciales y cuentas base. Ejecutalo una sola vez y luego elimina <code>public/install.php</code> y <code>.install.lock</code>.</p>

    <?php foreach ($messages as $message): ?>
        <div class="msg <?= str_contains($message, 'Error:') ? 'err' : 'ok' ?>"><?= $message ?></div>
    <?php endforeach; ?>

    <?php if (!$done): ?>
    <form method="POST">
        <div class="grid">
            <div>
                <label for="DB_HOST">DB Host</label>
                <input id="DB_HOST" name="DB_HOST" value="<?= h($defaults['DB_HOST']) ?>">
            </div>
            <div>
                <label for="DB_PORT">DB Port</label>
                <input id="DB_PORT" name="DB_PORT" value="<?= h($defaults['DB_PORT']) ?>">
            </div>
        </div>
        <label for="DB_DATABASE">Base de datos</label>
        <input id="DB_DATABASE" name="DB_DATABASE" value="<?= h($defaults['DB_DATABASE']) ?>">
        <label for="DB_USERNAME">Usuario</label>
        <input id="DB_USERNAME" name="DB_USERNAME" value="<?= h($defaults['DB_USERNAME']) ?>">
        <label for="DB_PASSWORD">Contraseña</label>
        <input id="DB_PASSWORD" name="DB_PASSWORD" type="password" value="<?= h($defaults['DB_PASSWORD']) ?>">
        <p class="small">Las cuentas iniciales quedan con contraseña <code>admin123</code>.</p>
        <button type="submit">Crear base y datos iniciales</button>
    </form>
    <?php else: ?>
        <div class="msg ok">Listo. Ya puedes entrar al sitio y borrar este instalador.</div>
    <?php endif; ?>
</div>
</body>
</html>
