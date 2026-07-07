<?php

use Phinx\Seed\AbstractSeed;

class DatabaseSeeder extends AbstractSeed
{
    public function run(): void
    {
        $pdo = $this->getAdapter()->getConnection();

        // Helper para insertar con ID explícito usando PDO
        $insert = function (string $table, array $rows) use ($pdo): void {
            foreach ($rows as $row) {
                $cols = implode(', ', array_map(fn($c) => "`$c`", array_keys($row)));
                $placeholders = implode(', ', array_fill(0, count($row), '?'));
                $stmt = $pdo->prepare("INSERT INTO `$table` ($cols) VALUES ($placeholders)");
                $stmt->execute(array_values($row));
            }
        };

        // 1. Roles
        $insert('roles', [
            ['id' => 1, 'name' => 'Super Administrador', 'description' => 'Acceso total'],
            ['id' => 2, 'name' => 'Marketing', 'description' => 'Gestión de contenidos y promociones'],
            ['id' => 3, 'name' => 'Operaciones', 'description' => 'Gestión de pedidos y despachos'],
            ['id' => 4, 'name' => 'Cliente', 'description' => 'Cliente registrado'],
            ['id' => 5, 'name' => 'Editor', 'description' => 'Gestión editorial del blog'],
        ]);

        // 2. Permisos
        $insert('permissions', [
            ['id' => 1, 'name' => 'manage_users', 'description' => 'Administrar usuarios del sistema'],
            ['id' => 2, 'name' => 'manage_cms', 'description' => 'Administrar páginas y bloques del CMS'],
            ['id' => 3, 'name' => 'manage_catalog', 'description' => 'Administrar productos y categorías'],
            ['id' => 4, 'name' => 'view_dashboard', 'description' => 'Ver indicadores del panel de control'],
            ['id' => 5, 'name' => 'manage_blog', 'description' => 'Administrar entradas del blog'],
        ]);

        // 3. Relaciones Rol-Permiso
        $insert('role_permissions', [
            ['role_id' => 1, 'permission_id' => 1],
            ['role_id' => 1, 'permission_id' => 2],
            ['role_id' => 1, 'permission_id' => 3],
            ['role_id' => 1, 'permission_id' => 4],
            ['role_id' => 2, 'permission_id' => 2],
            ['role_id' => 2, 'permission_id' => 5],
            ['role_id' => 5, 'permission_id' => 5],
        ]);

        // 4. Usuarios (contraseña: admin123)
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $insert('users', [
            ['id' => 1, 'email' => 'admin@caralbiotec.com', 'password_hash' => $hash, 'status' => 'active'],
            ['id' => 2, 'email' => 'marketing@caralbiotec.com', 'password_hash' => $hash, 'status' => 'active'],
            ['id' => 3, 'email' => 'operador@caralbiotec.com', 'password_hash' => $hash, 'status' => 'active'],
            ['id' => 4, 'email' => 'editor@caralbiotec.com', 'password_hash' => $hash, 'status' => 'active'],
        ]);

        // 5. Relaciones Usuario-Rol
        $insert('user_roles', [
            ['user_id' => 1, 'role_id' => 1],
            ['user_id' => 2, 'role_id' => 2],
            ['user_id' => 3, 'role_id' => 4],
            ['user_id' => 4, 'role_id' => 5],
        ]);

        // 6. Categorías
        $insert('categories', [
            ['id' => 1, 'name' => 'Nutracéuticos', 'slug' => 'nutraceuticos', 'description' => 'Suplementos para tu salud y nutrición', 'image_url' => '/uploads/cat_nutraceuticos.png'],
            ['id' => 2, 'name' => 'Bienestar', 'slug' => 'bienestar', 'description' => 'Descanso, confort y cuidado personal', 'image_url' => '/uploads/cat_bienestar.png'],
            ['id' => 3, 'name' => 'Rehabilitación', 'slug' => 'rehabilitacion', 'description' => 'Equipos y accesorios para tu recuperación', 'image_url' => '/uploads/cat_rehabilitacion.png'],
            ['id' => 4, 'name' => 'Apoyo al Paciente', 'slug' => 'apoyo-al-paciente', 'description' => 'Productos de apoyo para tu día a día', 'image_url' => '/uploads/cat_apoyo.png'],
        ]);

        // 7. Condiciones de Salud
        $insert('health_conditions', [
            ['id' => 1, 'name' => 'Cáncer', 'slug' => 'cancer', 'description' => 'Productos para el bienestar y cuidado integral', 'icon' => 'ribbon'],
            ['id' => 2, 'name' => 'Diabetes', 'slug' => 'diabetes', 'description' => 'Control, prevención y bienestar diario', 'icon' => 'droplet'],
            ['id' => 3, 'name' => 'Osteoporosis', 'slug' => 'osteoporosis', 'description' => 'Fortalece tus huesos y mejora tu calidad de vida', 'icon' => 'bone'],
            ['id' => 4, 'name' => 'Cardiológicos', 'slug' => 'cardiologicos', 'description' => 'Cuida tu corazón y mejora tu salud', 'icon' => 'heart'],
            ['id' => 5, 'name' => 'Adulto Mayor', 'slug' => 'adulto-mayor', 'description' => 'Bienestar, seguridad y confort diario', 'icon' => 'person'],
            ['id' => 6, 'name' => 'Rehabilitación', 'slug' => 'rehabilitacion-condicion', 'description' => 'Recupera tu movilidad y bienestar', 'icon' => 'activity'],
        ]);

        // 8. Productos
        $insert('products', [
            [
                'id' => 1, 'category_id' => 1,
                'name' => 'Colágeno Hidrolizado Premium', 'slug' => 'colageno-hidrolizado-premium',
                'sku' => 'NUT-COL-001',
                'short_description' => 'Colágeno de alta absorción para fortalecer articulaciones y piel.',
                'description' => 'Nuestro Colágeno Hidrolizado Premium está enriquecido con Vitamina C y Magnesio, diseñado especialmente para mejorar la elasticidad de la piel, fortalecer el cabello y brindar soporte estructural a tus articulaciones y huesos.',
                'price' => 89.90, 'stock' => 50, 'image_url' => '/uploads/colageno.jpg', 'is_active' => 1,
            ],
            [
                'id' => 2, 'category_id' => 3,
                'name' => 'Pistola de Masaje Vacufast', 'slug' => 'pistola-masaje-vacufast',
                'sku' => 'REH-VAC-001',
                'short_description' => 'Dispositivo de percusión profesional para aliviar dolores musculares.',
                'description' => 'La pistola de masaje Vacufast cuenta con 6 cabezales intercambiables y 20 niveles de velocidad. Ideal para la rehabilitación muscular.',
                'price' => 250.00, 'stock' => 15, 'image_url' => '/uploads/vacufast.jpg', 'is_active' => 1,
            ],
            [
                'id' => 3, 'category_id' => 2,
                'name' => 'Almohada Ergonómica Confort', 'slug' => 'almohada-ergonomica-confort',
                'sku' => 'BIE-ALM-001',
                'short_description' => 'Almohada de espuma viscoelástica para soporte cervical.',
                'description' => 'Diseñada anatómicamente para mantener alineada la columna cervical durante el descanso. Memory foam de alta densidad.',
                'price' => 120.00, 'stock' => 30, 'image_url' => '/uploads/almohada.jpg', 'is_active' => 1,
            ],
            [
                'id' => 4, 'category_id' => 4,
                'name' => 'Bastón Regulable de Aluminio', 'slug' => 'baston-regulable-aluminio',
                'sku' => 'APO-BAS-001',
                'short_description' => 'Bastón ligero de aluminio con mango ergonómico.',
                'description' => 'Bastón de apoyo regulable en altura con base antideslizante. Aluminio anodizado de alta resistencia.',
                'price' => 65.00, 'stock' => 40, 'image_url' => '/uploads/baston.jpg', 'is_active' => 1,
            ],
            [
                'id' => 5, 'category_id' => 1,
                'name' => 'Multivitamínico Oncológico Care', 'slug' => 'multivitaminico-oncologico-care',
                'sku' => 'NUT-ONC-001',
                'short_description' => 'Suplemento nutricional de apoyo integral para el sistema inmunológico.',
                'description' => 'Fórmula especial rica en antioxidantes, vitaminas y minerales clave para dar soporte nutricional y mantener las defensas activas.',
                'price' => 110.00, 'stock' => 25, 'image_url' => '/uploads/multivitaminico.jpg', 'is_active' => 1,
            ],
        ]);

        // 9. Relaciones Producto-Condiciones
        $insert('product_conditions', [
            ['product_id' => 1, 'health_condition_id' => 3],
            ['product_id' => 2, 'health_condition_id' => 6],
            ['product_id' => 4, 'health_condition_id' => 5],
            ['product_id' => 4, 'health_condition_id' => 6],
            ['product_id' => 5, 'health_condition_id' => 1],
        ]);
    }
}
