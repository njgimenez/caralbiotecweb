<?php

namespace Caral\Modules\Admin\Controllers;

use Caral\Core\Session;
use Caral\Core\Template;
use Caral\Modules\Admin\Services\CompanySettingsService;

class SettingsController
{
    private function requireAdmin(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit();
        }

        if (!in_array(Session::getUserRole(), ['Super Administrador', 'Operaciones', 'Marketing'], true)) {
            header('Location: /');
            exit();
        }
    }

    public function company(): void
    {
        $this->requireAdmin();

        echo Template::renderAdmin('settings/company', [
            'settings' => CompanySettingsService::get(),
        ]);
    }

    public function saveCompany(): void
    {
        $this->requireAdmin();

        try {
            CompanySettingsService::save($_POST);
            Session::set('flash_success', 'Configuración de empresa actualizada.');
            header('Location: /admin/configuracion/empresa');
        } catch (\Throwable $e) {
            echo Template::renderAdmin('settings/company', [
                'settings' => array_merge(CompanySettingsService::defaults(), $_POST),
                'error' => $e->getMessage(),
            ]);
        }
        exit();
    }
}
