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
            'tag_text' => 'Productos certificados Â· Lima, PerÃº',
            'title_part1' => 'Soluciones integrales para tu',
            'title_accent' => 'bienestar',
            'title_part2' => 'y recuperaciÃ³n',
            'subtitle' => 'NutracÃ©uticos, equipos de rehabilitaciÃ³n y productos de bienestar seleccionados por especialistas para mejorar tu calidad de vida.',
            'btn_primary_text' => 'Comprar ahora',
            'btn_primary_url' => '/productos',
            'btn_secondary_text' => 'Ver categorÃ­as',
            'btn_secondary_url' => '#categorias'
        ];
        $insert('cms_blocks', [
            'block_key' => 'home_hero',
            'title' => 'SecciÃ³n Hero Principal',
            'content_json' => json_encode($heroData, JSON_UNESCAPED_UNICODE),
            'is_active' => 1
        ]);

        // 2. Bloque Beneficios
        $benefitsData = [
            ['icon' => 'truck', 'title' => 'EnvÃ­os a todo el PerÃº', 'desc' => 'RÃ¡pidos y seguros'],
            ['icon' => 'shield-check', 'title' => 'Productos de calidad', 'desc' => 'GarantÃ­a y respaldo'],
            ['icon' => 'lock', 'title' => 'Compra 100% segura', 'desc' => 'Tus datos protegidos'],
            ['icon' => 'headset', 'title' => 'AtenciÃ³n personalizada', 'desc' => 'Te asesoramos siempre']
        ];
        $insert('cms_blocks', [
            'block_key' => 'home_benefits',
            'title' => 'Barra de Beneficios (Trust Badges)',
            'content_json' => json_encode($benefitsData, JSON_UNESCAPED_UNICODE),
            'is_active' => 1
        ]);

        // 3. Bloque CTA
        $ctaData = [
            'title' => 'Â¿Necesitas asesorÃ­a personalizada?',
            'subtitle' => 'Nuestro equipo de especialistas estÃ¡ listo para ayudarte a elegir el producto ideal.',
            'btn_text' => 'Chatear por WhatsApp',
            'btn_url' => 'https://wa.me/51939622005',
            'btn_icon' => 'whatsapp'
        ];
        $insert('cms_blocks', [
            'block_key' => 'home_cta',
            'title' => 'Llamado a la AcciÃ³n Personalizado (CTA)',
            'content_json' => json_encode($ctaData, JSON_UNESCAPED_UNICODE),
            'is_active' => 1
        ]);
        $defaultBlocks = [
            ['block_key' => 'home_brand', 'title' => 'Marca y recursos visuales', 'is_active' => 1],
            ['block_key' => 'home_categories', 'title' => 'Categorias del Home', 'is_active' => 0],
            ['block_key' => 'home_needs', 'title' => 'Compra segun tu necesidad', 'is_active' => 0],
            ['block_key' => 'home_featured_products', 'title' => 'Productos destacados', 'is_active' => 1],
            ['block_key' => 'home_blog', 'title' => 'Ultimas entradas del blog', 'is_active' => 1],
        ];

        foreach ($defaultBlocks as $block) {
            $insert('cms_blocks', [
                'block_key' => $block['block_key'],
                'title' => $block['title'],
                'content_json' => '{}',
                'is_active' => $block['is_active'],
            ]);
        }
    }
}
