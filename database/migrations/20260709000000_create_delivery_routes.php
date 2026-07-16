<?php

use Phinx\Migration\AbstractMigration;

class CreateDeliveryRoutes extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->execute("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->addOrderColumnIfMissing('delivery_zone', 'VARCHAR(30) NULL AFTER shipping_city');
        $this->addOrderColumnIfMissing('delivery_route_code', 'VARCHAR(20) NULL AFTER delivery_zone');
        $this->addOrderColumnIfMissing('delivery_route_name', 'VARCHAR(100) NULL AFTER delivery_route_code');
        $this->addOrderColumnIfMissing('delivery_time_min', 'SMALLINT UNSIGNED NULL AFTER delivery_route_name');
        $this->addOrderColumnIfMissing('delivery_time_max', 'SMALLINT UNSIGNED NULL AFTER delivery_time_min');

        $data = require __DIR__ . '/../data/delivery_routes.php';
        foreach ($data['routes'] as $route) {
            $this->execute(sprintf(
                "INSERT INTO delivery_rutas
                    (codigo, nombre, zona_cardinal, descripcion, tarifa, tiempo_min, tiempo_max, color, icono, hora_inicio, hora_fin, orden, activo)
                 VALUES
                    (%s, %s, %s, %s, %.2F, %d, %d, %s, %s, %s, %s, %d, 1)
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
                    activo = 1",
                $this->sqlQuote($route['codigo']),
                $this->sqlQuote($route['nombre']),
                $this->sqlQuote($route['zona_cardinal']),
                $this->sqlQuote($route['descripcion']),
                $route['tarifa'],
                $route['tiempo_min'],
                $route['tiempo_max'],
                $this->sqlQuote($route['color']),
                $this->sqlQuote($route['icono']),
                $this->sqlQuote($route['hora_inicio']),
                $this->sqlQuote($route['hora_fin']),
                $route['orden']
            ));
        }

        foreach ($data['districts'] as $district) {
            $this->execute(sprintf(
                "INSERT INTO delivery_distritos
                    (ubigeo, departamento, provincia, distrito, zona_cardinal, ruta_delivery, orden_ruta, activo)
                 VALUES
                    (%s, %s, %s, %s, %s, %s, %d, 1)
                 ON DUPLICATE KEY UPDATE
                    ubigeo = VALUES(ubigeo),
                    departamento = VALUES(departamento),
                    zona_cardinal = VALUES(zona_cardinal),
                    ruta_delivery = VALUES(ruta_delivery),
                    orden_ruta = VALUES(orden_ruta),
                    activo = 1",
                $this->sqlQuote($district['ubigeo']),
                $this->sqlQuote($district['departamento']),
                $this->sqlQuote($district['provincia']),
                $this->sqlQuote($district['distrito']),
                $this->sqlQuote($district['zona_cardinal']),
                $this->sqlQuote($district['ruta_delivery']),
                $district['orden_ruta']
            ));
        }
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS delivery_distritos");
        $this->execute("DROP TABLE IF EXISTS delivery_rutas");
        $this->execute("
            ALTER TABLE orders
                DROP COLUMN delivery_time_max,
                DROP COLUMN delivery_time_min,
                DROP COLUMN delivery_route_name,
                DROP COLUMN delivery_route_code,
                DROP COLUMN delivery_zone
        ");
    }

    private function addOrderColumnIfMissing(string $column, string $definition): void
    {
        if ($this->hasOrderColumn($column)) {
            return;
        }

        $this->execute("ALTER TABLE orders ADD COLUMN {$column} {$definition}");
    }

    private function hasOrderColumn(string $column): bool
    {
        $row = $this->fetchRow(sprintf(
            "SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'orders'
               AND COLUMN_NAME = %s",
            $this->sqlQuote($column)
        ));

        return (int)($row['total'] ?? 0) > 0;
    }

    private function sqlQuote(?string $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return "'" . str_replace("'", "''", $value) . "'";
    }
}
