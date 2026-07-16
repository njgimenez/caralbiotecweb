<?php

namespace Caral\Modules\Admin\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Core\Template;

class CmsAdminController
{
    private function requireAdmin(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit();
        }

        $role = Session::getUserRole();
        if (!in_array($role, ['Super Administrador', 'Marketing'])) {
            header('Location: /');
            exit();
        }
    }

    public function index(): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $blocks = $db->query('SELECT * FROM cms_blocks ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);

        echo Template::renderAdmin('cms/index', [
            'blocks' => $blocks,
        ]);
    }

    public function edit(string $key): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $stmt = $db->prepare('SELECT * FROM cms_blocks WHERE block_key = :key');
        $stmt->execute(['key' => $key]);
        $block = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$block) {
            Session::set('flash_error', 'Bloque de contenido no encontrado.');
            header('Location: /admin/cms');
            exit();
        }

        $content = json_decode((string)$block['content_json'], true);
        $content = is_array($content) ? $content : [];

        if ($key === 'home_hero') {
            echo Template::renderAdmin('cms/edit_hero', [
                'block' => $block,
                'content' => $content,
            ]);
            return;
        }

        if ($key === 'home_benefits') {
            echo Template::renderAdmin('cms/edit_benefits', [
                'block' => $block,
                'content' => $content,
            ]);
            return;
        }

        if ($key === 'home_cta') {
            echo Template::renderAdmin('cms/edit_cta', [
                'block' => $block,
                'content' => $content,
            ]);
            return;
        }

        echo Template::renderAdmin('cms/edit_generic', [
            'block' => $block,
            'content' => $content,
        ]);
    }

    public function update(string $key): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $stmt = $db->prepare('SELECT * FROM cms_blocks WHERE block_key = :key');
        $stmt->execute(['key' => $key]);
        $block = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$block) {
            Session::set('flash_error', 'Bloque no encontrado.');
            header('Location: /admin/cms');
            exit();
        }

        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $contentData = [];

        if ($key === 'home_hero') {
            $slides = [];
            $postedSlides = $_POST['slides'] ?? [];
            if (is_array($postedSlides)) {
                foreach ($postedSlides as $slide) {
                    if (!is_array($slide)) {
                        continue;
                    }
                    $titlePart1 = trim((string)($slide['title_part1'] ?? ''));
                    $titleAccent = trim((string)($slide['title_accent'] ?? ''));
                    $titlePart2 = trim((string)($slide['title_part2'] ?? ''));
                    $subtitle = trim((string)($slide['subtitle'] ?? ''));
                    $imageUrl = trim((string)($slide['image_url'] ?? ''));
                    if ($titlePart1 === '' && $titleAccent === '' && $titlePart2 === '' && $subtitle === '' && $imageUrl === '') {
                        continue;
                    }
                    $slides[] = [
                        'tag_text' => trim((string)($slide['tag_text'] ?? '')),
                        'title_part1' => $titlePart1,
                        'title_accent' => $titleAccent,
                        'title_part2' => $titlePart2,
                        'subtitle' => $subtitle,
                        'image_url' => $imageUrl,
                        'btn_primary_text' => trim((string)($slide['btn_primary_text'] ?? '')),
                        'btn_primary_url' => trim((string)($slide['btn_primary_url'] ?? '')),
                        'btn_secondary_text' => trim((string)($slide['btn_secondary_text'] ?? '')),
                        'btn_secondary_url' => trim((string)($slide['btn_secondary_url'] ?? '')),
                    ];
                }
            }

            if ($slides === []) {
                $slides[] = [
                    'tag_text' => 'Productos certificados - Lima, Peru',
                    'title_part1' => 'Soluciones integrales para tu',
                    'title_accent' => 'bienestar',
                    'title_part2' => 'y recuperacion',
                    'subtitle' => 'Nutraceuticos, equipos de rehabilitacion y productos de bienestar seleccionados por especialistas.',
                    'image_url' => '/images/hero-bg.png',
                    'btn_primary_text' => 'Comprar ahora',
                    'btn_primary_url' => '/productos',
                    'btn_secondary_text' => 'Ver categorias',
                    'btn_secondary_url' => '#categorias',
                ];
            }

            $contentData = ['slides' => $slides];
        } elseif ($key === 'home_benefits') {
            for ($i = 0; $i < 4; $i++) {
                $contentData[] = [
                    'icon' => trim($_POST["icon_$i"] ?? 'check-circle'),
                    'title' => trim($_POST["title_$i"] ?? ''),
                    'desc' => trim($_POST["desc_$i"] ?? ''),
                ];
            }
        } elseif ($key === 'home_cta') {
            $contentData = [
                'title' => trim($_POST['title'] ?? ''),
                'subtitle' => trim($_POST['subtitle'] ?? ''),
                'btn_text' => trim($_POST['btn_text'] ?? ''),
                'btn_url' => trim($_POST['btn_url'] ?? ''),
                'btn_icon' => trim($_POST['btn_icon'] ?? 'chat'),
            ];
        } else {
            $json = trim((string)($_POST['content_json'] ?? '{}'));
            $json = $json === '' ? '{}' : $json;
            $decoded = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Session::set('flash_error', 'El contenido JSON del bloque no es valido: ' . json_last_error_msg());
                header('Location: /admin/cms/' . rawurlencode($key) . '/editar');
                exit();
            }

            $contentData = $decoded;
        }

        $title = trim((string)($_POST['block_title'] ?? $block['title']));
        $title = $title !== '' ? $title : $block['title'];

        $updateStmt = $db->prepare('
            UPDATE cms_blocks
            SET title = :title, content_json = :json, is_active = :active
            WHERE block_key = :key
        ');
        $updateStmt->execute([
            'title' => $title,
            'json' => json_encode($contentData, JSON_UNESCAPED_UNICODE),
            'active' => $isActive,
            'key' => $key,
        ]);

        Session::set('flash_success', "Bloque {$title} actualizado correctamente.");
        header('Location: /admin/cms');
        exit();
    }
}