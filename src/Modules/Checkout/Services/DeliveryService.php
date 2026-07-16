<?php

namespace Caral\Modules\Checkout\Services;

use PDO;
use Caral\Core\Database;

class DeliveryService
{
    private const PROVINCE_HANDLING_FEE = 15.00;

    public static function provinceHandlingFee(): float
    {
        return self::PROVINCE_HANDLING_FEE;
    }

    public static function normalizeCity(string $city): string
    {
        $city = trim($city);
        return $city !== '' ? $city : 'Lima';
    }

    public static function isLimaCity(string $city): bool
    {
        $normalized = strtolower(self::normalizeCity($city));
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        $normalized = $ascii !== false ? $ascii : $normalized;

        return in_array($normalized, ['lima', 'lima metropolitana', 'callao'], true);
    }


    public static function peruCities(): array
    {
        return [
            'Lima', 'Abancay', 'Andahuaylas', 'Arequipa',
            'Ayacucho', 'Bagua', 'Cajamarca',
            'Camana', 'Casma', 'Cerro de Pasco', 'Chachapoyas',
            'Chepen', 'Chiclayo', 'Chimbote', 'Chincha Alta',
            'Chota', 'Cusco', 'Ferrenafe', 'Huancavelica',
            'Huancayo', 'Huanta', 'Huanuco', 'Huaraz',
            'Huarmey', 'Ica', 'Ilave', 'Ilo',
            'Iquitos', 'Jaen', 'Jauja', 'Juliaca',
            'La Merced', 'Lambayeque', 'Mollendo', 'Moquegua',
            'Moyobamba', 'Nauta', 'Nazca', 'Nuevo Chimbote',
            'Oxapampa', 'Pacasmayo', 'Paita', 'Pisco',
            'Piura', 'Pucallpa', 'Puerto Maldonado', 'Puno',
            'Quillabamba', 'Rioja', 'Sicuani', 'Sullana',
            'Tacna', 'Talara', 'Tarapoto', 'Tarma',
            'Tingo Maria', 'Trujillo', 'Tumbes', 'Yurimaguas',
            'Zarumilla',
        ];
    }

    public static function provinceCourierOptions(): array
    {
        return ['OLVA', 'SHALOM'];
    }

    public static function activeDistricts(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT
                d.ubigeo,
                d.distrito,
                d.zona_cardinal,
                d.ruta_delivery,
                d.orden_ruta,
                r.nombre AS ruta_nombre,
                r.tarifa,
                r.tiempo_min,
                r.tiempo_max
            FROM delivery_distritos d
            JOIN delivery_rutas r ON r.codigo = d.ruta_delivery
            WHERE d.activo = 1
              AND r.activo = 1
            ORDER BY r.orden ASC, d.orden_ruta ASC, d.distrito ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findDistrict(string $district): ?array
    {
        $district = trim($district);
        if ($district === '') {
            return null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT
                d.ubigeo,
                d.departamento,
                d.provincia,
                d.distrito,
                d.zona_cardinal,
                d.ruta_delivery,
                r.nombre AS ruta_nombre,
                r.tarifa,
                r.tiempo_min,
                r.tiempo_max
            FROM delivery_distritos d
            JOIN delivery_rutas r ON r.codigo = d.ruta_delivery
            WHERE d.activo = 1
              AND r.activo = 1
              AND d.distrito = :district
            LIMIT 1
        ");
        $stmt->execute(['district' => $district]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function publicOptions(): array
    {
        $options = array_map(static function (array $district): array {
            return [
                'district' => (string)$district['distrito'],
                'zone' => (string)$district['zona_cardinal'],
                'route' => (string)$district['ruta_delivery'],
                'routeName' => (string)$district['ruta_nombre'],
                'fee' => (float)$district['tarifa'],
                'timeMin' => (int)$district['tiempo_min'],
                'timeMax' => (int)$district['tiempo_max'],
            ];
        }, self::activeDistricts());

        usort($options, static fn(array $a, array $b): int => strcasecmp($a['district'], $b['district']));
        return $options;
    }
}
