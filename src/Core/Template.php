<?php

namespace Caral\Core;

use League\Plates\Engine;

class Template
{
    private static function buildEngine(string $directory): Engine
    {
        $engine = new Engine($directory);

        // Directorios compartidos
        $engine->addFolder('shared', __DIR__ . '/../Modules/CMS/Views');
        $engine->addFolder('admin',  __DIR__ . '/../Modules/Admin/Views');

        // Helpers globales
        $engine->registerFunction('isLoggedIn', function () {
            return Session::isLoggedIn();
        });
        $engine->registerFunction('getUserEmail', function () {
            return Session::getUserEmail();
        });
        $engine->registerFunction('getUserRole', function () {
            return Session::getUserRole();
        });
        $engine->registerFunction('getCartCount', function () {
            return \Caral\Modules\Cart\Services\CartService::getCartCount();
        });
        $engine->registerFunction('companySettings', function () {
            return \Caral\Modules\Admin\Services\CompanySettingsService::get();
        });
        $engine->registerFunction('companyPhone', function () {
            return \Caral\Modules\Admin\Services\CompanySettingsService::phoneDisplay();
        });
        $engine->registerFunction('companyWhatsappUrl', function () {
            return \Caral\Modules\Admin\Services\CompanySettingsService::whatsappUrl();
        });
        $engine->registerFunction('companyEmail', function () {
            return \Caral\Modules\Admin\Services\CompanySettingsService::get()['email'] ?? '';
        });
        $engine->registerFunction('companyAddress', function () {
            return \Caral\Modules\Admin\Services\CompanySettingsService::get()['address'] ?? '';
        });

        return $engine;
    }

    /**
     * Renderiza una vista del frontend (módulo CMS, Catalog, Auth, Cart).
     */
    public static function render(string $module, string $view, array $data = []): string
    {
        $directory = __DIR__ . '/../Modules/' . $module . '/Views';
        $engine    = self::buildEngine($directory);
        return $engine->render($view, $data);
    }

    /**
     * Renderiza una vista del panel de administración (Admin/Views).
     */
    public static function renderAdmin(string $view, array $data = []): string
    {
        $directory = __DIR__ . '/../Modules/Admin/Views';
        $engine    = self::buildEngine($directory);
        return $engine->render($view, $data);
    }
}
