<?php

namespace Caral\Modules\Admin\Services;

use PDO;
use Caral\Core\Database;

class CompanySettingsService
{
    public const KEY = 'company_settings';

    public static function defaults(): array
    {
        return [
            'business_name' => 'Caral Biotec',
            'trade_name' => 'Caral Biotec',
            'ruc' => '',
            'address' => 'Lima, Perú',
            'phone' => '',
            'email' => 'contacto@caralbiotec.com',
            'igv_percent' => 18.00,
        ];
    }

    public static function get(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
        $stmt->execute(['key' => self::KEY]);
        $raw = $stmt->fetchColumn();
        $settings = $raw ? json_decode((string)$raw, true) : [];

        return array_merge(self::defaults(), is_array($settings) ? $settings : []);
    }

    public static function save(array $settings): void
    {
        $clean = [
            'business_name' => trim((string)($settings['business_name'] ?? '')),
            'trade_name' => trim((string)($settings['trade_name'] ?? '')),
            'ruc' => preg_replace('/\D+/', '', (string)($settings['ruc'] ?? '')),
            'address' => trim((string)($settings['address'] ?? '')),
            'phone' => trim((string)($settings['phone'] ?? '')),
            'email' => trim((string)($settings['email'] ?? '')),
            'igv_percent' => round((float)($settings['igv_percent'] ?? 18), 2),
        ];

        if ($clean['business_name'] === '') {
            throw new \InvalidArgumentException('La razón social es obligatoria.');
        }
        if ($clean['ruc'] !== '' && !preg_match('/^\d{11}$/', $clean['ruc'])) {
            throw new \InvalidArgumentException('El RUC debe tener 11 dígitos.');
        }
        if ($clean['igv_percent'] < 0 || $clean['igv_percent'] > 100) {
            throw new \InvalidArgumentException('El IGV debe estar entre 0 y 100.');
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([
            'key' => self::KEY,
            'value' => json_encode($clean, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public static function splitTax(float $total, float $igvPercent): array
    {
        $factor = 1 + ($igvPercent / 100);
        $taxable = $factor > 0 ? round($total / $factor, 2) : $total;
        $tax = round($total - $taxable, 2);

        return [
            'taxable' => $taxable,
            'tax' => $tax,
            'total' => round($total, 2),
        ];
    }
}
