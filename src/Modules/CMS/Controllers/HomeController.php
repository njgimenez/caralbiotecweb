<?php

namespace Caral\Modules\CMS\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Template;

class HomeController
{
    public function index(): void
    {
        try {
            $db = Database::getConnection();

            // 1. Obtener todas las categorías
            $stmt = $db->query("SELECT * FROM categories ORDER BY id ASC");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Obtener todas las condiciones de salud
            $stmt = $db->query("SELECT * FROM health_conditions ORDER BY id ASC");
            $conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Obtener productos destacados para la página de inicio
            $stmt = $db->query("
                SELECT p.*, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 
                LIMIT 4
            ");
            $featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Obtener bloques de CMS
            $cmsStmt = $db->query("SELECT block_key, content_json, is_active FROM cms_blocks");
            $cmsRows = $cmsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $cmsBlocks = [];
            foreach ($cmsRows as $row) {
                $cmsBlocks[$row['block_key']] = [
                    'content' => json_decode($row['content_json'], true),
                    'is_active' => (bool)$row['is_active']
                ];
            }

            // 5. Últimas entradas publicadas del blog
            $blogStmt = $db->query("
                SELECT id, title, slug, excerpt, featured_image_url, tags, published_at
                FROM blog_posts
                WHERE status = 'published'
                  AND published_at IS NOT NULL
                  AND published_at <= NOW()
                ORDER BY published_at DESC, id DESC
                LIMIT 3
            ");
            $latestPosts = $blogStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Valores por defecto en caso de error o base de datos no configurada
            $categories = [];
            $conditions = [];
            $featuredProducts = [];
            $cmsBlocks = [];
            $latestPosts = [];
        }

        // Definir fallbacks por si acaso no hay datos en cms_blocks
        $hero = $cmsBlocks['home_hero'] ?? [
            'is_active' => true,
            'content' => [
                'tag_text' => 'Productos certificados · Lima, Perú',
                'title_part1' => 'Soluciones integrales para tu',
                'title_accent' => 'bienestar',
                'title_part2' => 'y recuperación',
                'subtitle' => 'Nutracéuticos, equipos de rehabilitación y productos de bienestar seleccionados por especialistas para mejorar tu calidad de vida.',
                'btn_primary_text' => 'Comprar ahora',
                'btn_primary_url' => '/productos',
                'btn_secondary_text' => 'Ver categorías',
                'btn_secondary_url' => '#categorias'
            ]
        ];

        $benefits = $cmsBlocks['home_benefits'] ?? [
            'is_active' => true,
            'content' => [
                ['icon' => 'truck', 'title' => 'Envíos a todo el Perú', 'desc' => 'Rápidos y seguros'],
                ['icon' => 'shield-check', 'title' => 'Productos de calidad', 'desc' => 'Garantía y respaldo'],
                ['icon' => 'lock', 'title' => 'Compra 100% segura', 'desc' => 'Tus datos protegidos'],
                ['icon' => 'headset', 'title' => 'Atención personalizada', 'desc' => 'Te asesoramos siempre']
            ]
        ];

        $cta = $cmsBlocks['home_cta'] ?? [
            'is_active' => true,
            'content' => [
                'title' => '¿Necesitas asesoría personalizada?',
                'subtitle' => 'Nuestro equipo de especialistas está listo para ayudarte a elegir el producto ideal.',
                'btn_text' => 'Chatear por WhatsApp',
                'btn_url' => 'https://wa.me/51947123456',
                'btn_icon' => 'whatsapp'
            ]
        ];

        // Renderizar la vista home
        echo Template::render('CMS', 'home', [
            'categories' => $categories,
            'conditions' => $conditions,
            'featuredProducts' => $featuredProducts,
            'latestPosts' => $latestPosts,
            'hero' => $hero,
            'benefits' => $benefits,
            'cta' => $cta
        ]);
    }
}
