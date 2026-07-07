<?php

require __DIR__ . '/../vendor/autoload.php';

use Caral\Core\Session;
use Bramus\Router\Router;

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Iniciar sesión segura
Session::start();

// Configurar el enrutador
$router = new Router();
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$router->setBasePath(str_starts_with($requestPath, '/public/') || $requestPath === '/public' ? '/public' : '');
$router->setNamespace('\Caral\Modules');

// ── Rutas públicas ─────────────────────────────────────────────
$router->get('/',  'CMS\Controllers\HomeController@index');

// Autenticación
$router->get('/login',  'Auth\Controllers\LoginController@showLoginForm');
$router->post('/login', 'Auth\Controllers\LoginController@login');
$router->get('/logout', 'Auth\Controllers\LoginController@logout');

// Catálogo
$router->get('/productos',         'Catalog\Controllers\ProductController@index');
$router->get('/producto/([^/]+)',   'Catalog\Controllers\ProductController@show');

// Blog
$router->get('/blog',              'Blog\Controllers\BlogController@index');
$router->get('/blog/([^/]+)',      'Blog\Controllers\BlogController@show');

// Carrito
$router->get('/carrito',                      'Cart\Controllers\CartController@index');
$router->post('/carrito/agregar',             'Cart\Controllers\CartController@add');
$router->post('/carrito/actualizar',          'Cart\Controllers\CartController@update');
$router->get('/carrito/eliminar/([0-9]+)',    'Cart\Controllers\CartController@delete');

// Checkout y pago
$router->get('/checkout',                     'Checkout\Controllers\CheckoutController@show');
$router->post('/checkout/procesar',           'Checkout\Controllers\CheckoutController@process');
$router->get('/checkout/confirmacion',        'Checkout\Controllers\CheckoutController@confirmation');
$router->get('/checkout/error',               'Checkout\Controllers\CheckoutController@paymentError');

// ── Rutas del BackOffice /admin ────────────────────────────────
$router->get('/admin',  'Admin\Controllers\DashboardController@index');
$router->get('/admin/pos',  'Admin\Controllers\PosController@index');
$router->get('/admin/pos/ventas',  'Admin\Controllers\PosController@sales');
$router->get('/admin/pos/reporte',  'Admin\Controllers\PosController@report');
$router->post('/admin/pos/venta',  'Admin\Controllers\PosController@store');
$router->get('/admin/pos/comprobante/(\d+)',  'Admin\Controllers\PosController@receipt');

// Productos (admin)
$router->get('/admin/productos',                        'Admin\Controllers\ProductAdminController@index');
$router->get('/admin/productos/nuevo',                  'Admin\Controllers\ProductAdminController@create');
$router->post('/admin/productos/guardar',               'Admin\Controllers\ProductAdminController@store');
$router->get('/admin/productos/(\d+)/editar',           'Admin\Controllers\ProductAdminController@edit');
$router->post('/admin/productos/(\d+)/actualizar',      'Admin\Controllers\ProductAdminController@update');
$router->post('/admin/productos/(\d+)/eliminar',        'Admin\Controllers\ProductAdminController@destroy');
$router->post('/admin/upload-imagen',                   'Admin\Controllers\ImageUploadController@upload');

// Categorías (admin)
$router->get('/admin/categorias',                       'Admin\Controllers\CategoryAdminController@index');
$router->get('/admin/categorias/nueva',                 'Admin\Controllers\CategoryAdminController@create');
$router->post('/admin/categorias/guardar',              'Admin\Controllers\CategoryAdminController@store');
$router->get('/admin/categorias/(\d+)/editar',          'Admin\Controllers\CategoryAdminController@edit');
$router->post('/admin/categorias/(\d+)/actualizar',     'Admin\Controllers\CategoryAdminController@update');
$router->post('/admin/categorias/(\d+)/eliminar',       'Admin\Controllers\CategoryAdminController@destroy');

// CMS / Vistas (admin)
$router->get('/admin/cms',                              'Admin\Controllers\CmsAdminController@index');
$router->get('/admin/cms/([^/]+)/editar',               'Admin\Controllers\CmsAdminController@edit');
$router->post('/admin/cms/([^/]+)/actualizar',           'Admin\Controllers\CmsAdminController@update');

// Blog (admin/editor)
$router->get('/admin/blog',                             'Admin\Controllers\BlogAdminController@index');
$router->get('/admin/blog/nuevo',                       'Admin\Controllers\BlogAdminController@create');
$router->post('/admin/blog/guardar',                    'Admin\Controllers\BlogAdminController@store');
$router->get('/admin/blog/(\d+)/editar',                'Admin\Controllers\BlogAdminController@edit');
$router->post('/admin/blog/(\d+)/actualizar',           'Admin\Controllers\BlogAdminController@update');
$router->post('/admin/blog/(\d+)/eliminar',             'Admin\Controllers\BlogAdminController@destroy');

// Órdenes (admin)
$router->get('/admin/ordenes',                          'Admin\Controllers\OrderAdminController@index');
$router->get('/admin/ordenes/(\d+)',                    'Admin\Controllers\OrderAdminController@show');
$router->post('/admin/ordenes/(\d+)/estado',            'Admin\Controllers\OrderAdminController@updateStatus');

// Configuración (admin)
$router->get('/admin/configuracion/empresa',            'Admin\Controllers\SettingsController@company');
$router->post('/admin/configuracion/empresa',           'Admin\Controllers\SettingsController@saveCompany');

// Ejecutar
$router->run();
