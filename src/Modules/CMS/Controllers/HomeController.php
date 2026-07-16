<?php

namespace Caral\Modules\CMS\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Template;
use Caral\Modules\Admin\Services\CompanySettingsService;

class HomeController
{
    public function index(): void
    {
        $categories = [];
        $conditions = [];
        $featuredProducts = [];
        $cmsBlocks = [];
        $latestPosts = [];

        try {
            $db = Database::getConnection();
            $cmsRows = $db->query('SELECT block_key, content_json, is_active FROM cms_blocks')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cmsRows as $row) {
                $decoded = json_decode((string)$row['content_json'], true);
                $cmsBlocks[$row['block_key']] = [
                    'content' => is_array($decoded) ? $decoded : [],
                    'is_active' => (bool)$row['is_active'],
                ];
            }

            $categories = $db->query('SELECT * FROM categories ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
            $conditions = $db->query('SELECT * FROM health_conditions ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);

            $featuredProducts = $db->query('
                SELECT p.*, c.name as category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1
                LIMIT 4
            ')->fetchAll(PDO::FETCH_ASSOC);

            $latestPosts = $db->query('
                SELECT id, title, slug, excerpt, featured_image_url, tags, published_at
                FROM blog_posts
                WHERE status = "published" AND published_at IS NOT NULL AND published_at <= NOW()
                ORDER BY published_at DESC, id DESC
                LIMIT 3
            ')->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('No se pudieron cargar todos los datos del Home: ' . $e->getMessage());
        }

        $hero = $cmsBlocks['home_hero'] ?? ['is_active' => true, 'content' => self::defaultHeroContent()];
        $hero['content'] = self::normalizeHeroContent($hero['content'] ?? []);

        $benefits = $cmsBlocks['home_benefits'] ?? [
            'is_active' => true,
            'content' => [
                ['icon' => 'truck', 'title' => 'Envios a todo el Peru', 'desc' => 'Rapidos y seguros'],
                ['icon' => 'shield-check', 'title' => 'Productos de calidad', 'desc' => 'Garantia y respaldo'],
                ['icon' => 'lock', 'title' => 'Compra 100% segura', 'desc' => 'Tus datos protegidos'],
                ['icon' => 'headset', 'title' => 'Atencion personalizada', 'desc' => 'Te asesoramos siempre'],
            ],
        ];

        $cta = $cmsBlocks['home_cta'] ?? [
            'is_active' => true,
            'content' => [
                'title' => 'Necesitas asesoria personalizada?',
                'subtitle' => 'Nuestro equipo de especialistas esta listo para ayudarte a elegir el producto ideal.',
                'btn_text' => 'Chatear por WhatsApp',
                'btn_url' => CompanySettingsService::whatsappUrl(),
                'btn_icon' => 'whatsapp',
            ],
        ];

        if (($cta['content']['btn_icon'] ?? '') === 'whatsapp') {
            $cta['content']['btn_url'] = CompanySettingsService::whatsappUrl();
        }

        $categoriesSection = $cmsBlocks['home_categories'] ?? ['is_active' => true, 'content' => []];
        $needsSection = $cmsBlocks['home_needs'] ?? ['is_active' => true, 'content' => []];
        $featuredProductsSection = $cmsBlocks['home_featured_products'] ?? ['is_active' => true, 'content' => []];
        $blogSection = $cmsBlocks['home_blog'] ?? ['is_active' => true, 'content' => []];

        echo Template::render('CMS', 'home', [
            'categories' => $categories,
            'conditions' => $conditions,
            'featuredProducts' => $featuredProducts,
            'latestPosts' => $latestPosts,
            'hero' => $hero,
            'benefits' => $benefits,
            'cta' => $cta,
            'categoriesSection' => $categoriesSection,
            'needsSection' => $needsSection,
            'featuredProductsSection' => $featuredProductsSection,
            'blogSection' => $blogSection,
        ]);
    }

    public function about(): void
    {
        echo Template::render('CMS', 'about', [
            'company' => CompanySettingsService::get(),
        ]);
    }

    public function contact(): void
    {
        echo Template::render('CMS', 'contact', [
            'company' => CompanySettingsService::get(),
            'whatsappUrl' => CompanySettingsService::whatsappUrl(),
        ]);
    }

    private static function defaultHeroContent(): array
    {
        return [
            'slides' => [[
                'tag_text' => 'Productos certificados - Lima, Peru',
                'title_part1' => 'Soluciones integrales para tu',
                'title_accent' => 'bienestar',
                'title_part2' => 'y recuperacion',
                'subtitle' => 'Nutraceuticos, equipos de rehabilitacion y productos de bienestar seleccionados por especialistas para mejorar tu calidad de vida.',
                'image_url' => '/images/hero-bg.png',
                'btn_primary_text' => 'Comprar ahora',
                'btn_primary_url' => '/productos',
                'btn_secondary_text' => 'Ver categorias',
                'btn_secondary_url' => '#categorias',
            ]],
        ];
    }

    private static function normalizeHeroContent(array $content): array
    {
        if (!empty($content['slides']) && is_array($content['slides'])) {
            return $content;
        }

        return ['slides' => [[
            'tag_text' => $content['tag_text'] ?? 'Productos certificados - Lima, Peru',
            'title_part1' => $content['title_part1'] ?? 'Soluciones integrales para tu',
            'title_accent' => $content['title_accent'] ?? 'bienestar',
            'title_part2' => $content['title_part2'] ?? 'y recuperacion',
            'subtitle' => $content['subtitle'] ?? '',
            'image_url' => $content['image_url'] ?? '/images/hero-bg.png',
            'btn_primary_text' => $content['btn_primary_text'] ?? 'Comprar ahora',
            'btn_primary_url' => $content['btn_primary_url'] ?? '/productos',
            'btn_secondary_text' => $content['btn_secondary_text'] ?? 'Ver categorias',
            'btn_secondary_url' => $content['btn_secondary_url'] ?? '#categorias',
        ]]];
    }
}