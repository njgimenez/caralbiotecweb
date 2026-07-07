<?php

require __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno si existe el archivo .env
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'name' => $_ENV['DB_DATABASE'] ?? 'caralweb',
            'user' => $_ENV['DB_USERNAME'] ?? 'caraluser',
            'pass' => $_ENV['DB_PASSWORD'] ?? 'caralpassword',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'charset' => 'utf8mb4',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'name' => $_ENV['DB_DATABASE'] ?? 'caralweb',
            'user' => $_ENV['DB_USERNAME'] ?? 'caraluser',
            'pass' => $_ENV['DB_PASSWORD'] ?? 'caralpassword',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'charset' => 'utf8mb4',
        ],
    ],
    'version_order' => 'creation'
];
