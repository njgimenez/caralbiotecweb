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

    // GET /admin/cms
    public function index(): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $blocks = $db->query("SELECT * FROM cms_blocks ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        echo Template::renderAdmin('cms/index', [
            'blocks' => $blocks
        ]);
    }

    // GET /admin/cms/{key}/editar
    public function edit(string $key): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM cms_blocks WHERE block_key = :key");
        $stmt->execute(['key' => $key]);
        $block = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$block) {
            Session::set('flash_error', 'Bloque de contenido no encontrado.');
            header('Location: /admin/cms');
            exit();
        }

        $content = json_decode($block['content_json'], true);

        // Renderizar vista específica según el tipo de bloque
        if ($key === 'home_hero') {
            echo Template::renderAdmin('cms/edit_hero', [
                'block' => $block,
                'content' => $content
            ]);
        } elseif ($key === 'home_benefits') {
            echo Template::renderAdmin('cms/edit_benefits', [
                'block' => $block,
                'content' => $content
            ]);
        } elseif ($key === 'home_cta') {
            echo Template::renderAdmin('cms/edit_cta', [
                'block' => $block,
                'content' => $content
            ]);
        } else {
            Session::set('flash_error', 'Tipo de bloque no editable.');
            header('Location: /admin/cms');
            exit();
        }
    }

    // POST /admin/cms/{key}/actualizar
    public function update(string $key): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM cms_blocks WHERE block_key = :key");
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
            $contentData = [
                'tag_text' => trim($_POST['tag_text'] ?? ''),
                'title_part1' => trim($_POST['title_part1'] ?? ''),
                'title_accent' => trim($_POST['title_accent'] ?? ''),
                'title_part2' => trim($_POST['title_part2'] ?? ''),
                'subtitle' => trim($_POST['subtitle'] ?? ''),
                'btn_primary_text' => trim($_POST['btn_primary_text'] ?? ''),
                'btn_primary_url' => trim($_POST['btn_primary_url'] ?? ''),
                'btn_secondary_text' => trim($_POST['btn_secondary_text'] ?? ''),
                'btn_secondary_url' => trim($_POST['btn_secondary_url'] ?? ''),
            ];
        } elseif ($key === 'home_benefits') {
            // Un array de 4 beneficios
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
        }

        $updateStmt = $db->prepare("
            UPDATE cms_blocks 
            SET content_json = :json, is_active = :active 
            WHERE block_key = :key
        ");
        $updateStmt->execute([
            'json' => json_encode($contentData, JSON_UNESCAPED_UNICODE),
            'active' => $isActive,
            'key' => $key
        ]);

        Session::set('flash_success', "Bloque «{$block['title']}» actualizado correctamente.");
        header('Location: /admin/cms');
        exit();
    }
}
