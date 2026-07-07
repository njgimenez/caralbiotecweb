<?php

use Phinx\Seed\AbstractSeed;

class CmsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $pdo = $this->getAdapter()->getConnection();

        // Limpiar para evitar duplicados
        $pdo->exec("DELETE FROM cms_blocks");

        $insert = function (string $table, array $row) use ($pdo): void {
            $cols = implode(', ', array_map(fn($c) => "`$c`", array_keys($row)));
            $placeholders = implode(', ', array_fill(0, count($row), '?'));
            $stmt = $pdo->prepare("INSERT INTO `$table` ($cols) VALUES ($placeholders)");
            $stmt->execute(array_values($row));
        };

        // 1. Bloque Hero
        $heroData = [
            'tag_text' => 'Productos certificados · Lima, Perú',
            'title_part1' => 'Soluciones integrales para tu',
            'title_accent' => 'bienestar',
            'title_part2' => 'y recuperación',
            'subtitle' => 'Nutracéuticos, equipos de rehabilitación y productos de bienestar seleccionados por especialistas para mejorar tu calidad de vida.',
            'btn_primary_text' => 'Comprar ahora',
            'btn_primary_url' => '/productos',
            'btn_secondary_text' => 'Ver categorías',
            'btn_secondary_url' => '#categorias'
        ];
        $insert('cms_blocks', [
            'block_key' => 'home_hero',
            'title' => 'Sección Hero Principal',
            'content_json' => json_encode($heroData, JSON_UNESCAPED_UNICODE),
            'is_active' => 1
        ]);

        // 2. Bloque Beneficios
        $benefitsData = [
            ['icon' => 'truck', 'title' => 'Envíos a todo el Perú', 'desc' => 'Rápidos y seguros'],
            ['icon' => 'shield-check', 'title' => 'Productos de calidad', 'desc' => 'Garantía y respaldo'],
            ['icon' => 'lock', 'title' => 'Compra 100% segura', 'desc' => 'Tus datos protegidos'],
            ['icon' => 'headset', 'title' => 'Atención personalizada', 'desc' => 'Te asesoramos siempre']
        ];
        $insert('cms_blocks', [
            'block_key' => 'home_benefits',
            'title' => 'Barra de Beneficios (Trust Badges)',
            'content_json' => json_encode($benefitsData, JSON_UNESCAPED_UNICODE),
            'is_active' => 1
        ]);

        // 3. Bloque CTA
        $ctaData = [
            'title' => '¿Necesitas asesoría personalizada?',
            'subtitle' => 'Nuestro equipo de especialistas está listo para ayudarte a elegir el producto ideal.',
            'btn_text' => 'Chatear por WhatsApp',
            'btn_url' => 'https://wa.me/51947123456',
            'btn_icon' => 'whatsapp'
        ];
        $insert('cms_blocks', [
            'block_key' => 'home_cta',
            'title' => 'Llamado a la Acción Personalizado (CTA)',
            'content_json' => json_encode($ctaData, JSON_UNESCAPED_UNICODE),
            'is_active' => 1
        ]);
    }
}
