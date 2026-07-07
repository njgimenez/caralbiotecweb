<?php

namespace Caral\Modules\Auth\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Core\Template;

class LoginController
{
    public function showLoginForm(): void
    {
        if (Session::isLoggedIn()) {
            if (Session::getUserEmail() === 'operador@caralbiotec.com') {
                header('Location: /admin/pos');
            } elseif ((Session::getUserRole() ?? 'Cliente') === 'Editor') {
                header('Location: /admin/blog');
            } elseif (in_array(Session::getUserRole() ?? 'Cliente', ['Super Administrador', 'Marketing', 'Operaciones'])) {
                header('Location: /admin');
            } else {
                header('Location: /');
            }
            exit();
        }

        echo Template::render('Auth', 'login');
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if (empty($email) || empty($password)) {
            $errors[] = 'Por favor, ingrese su correo y contraseña.';
        } else {
            try {
                $db = Database::getConnection();

                // Buscar usuario y obtener su rol
                $stmt = $db->prepare("
                    SELECT u.*, r.name as role_name 
                    FROM users u
                    LEFT JOIN user_roles ur ON u.id = ur.user_id
                    LEFT JOIN roles r ON ur.role_id = r.id
                    WHERE u.email = :email
                ");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password_hash'])) {
                    if ($user['status'] !== 'active') {
                        $errors[] = 'Su cuenta está inactiva o bloqueada.';
                    } else {
                        // Iniciar sesión
                        Session::login($user);

                        // Registrar en auditoría
                        $auditStmt = $db->prepare("
                            INSERT INTO audit_logs (user_id, action, entity, entity_id, description, ip_address, user_agent) 
                            VALUES (:user_id, :action, :entity, :entity_id, :description, :ip, :ua)
                        ");
                        $auditStmt->execute([
                            'user_id' => $user['id'],
                            'action' => 'login_success',
                            'entity' => 'users',
                            'entity_id' => $user['id'],
                            'description' => 'Inicio de sesión exitoso desde el frontend',
                            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                        ]);

                        if (($user['email'] ?? '') === 'operador@caralbiotec.com') {
                            header('Location: /admin/pos');
                        } elseif (($user['role_name'] ?? 'Cliente') === 'Editor') {
                            header('Location: /admin/blog');
                        } elseif (in_array($user['role_name'] ?? 'Cliente', ['Super Administrador', 'Marketing', 'Operaciones'])) {
                            header('Location: /admin');
                        } else {
                            header('Location: /');
                        }
                        exit();
                    }
                } else {
                    $errors[] = 'Credenciales incorrectas. Intente nuevamente.';
                }
            } catch (\Exception $e) {
                $errors[] = 'Ocurrió un error en el servidor. Intente más tarde.';
            }
        }

        // Renderizar vista con errores
        echo Template::render('Auth', 'login', ['errors' => $errors]);
    }

    public function logout(): void
    {
        $userId = Session::getUserId();
        if ($userId) {
            try {
                $db = Database::getConnection();
                $auditStmt = $db->prepare("
                    INSERT INTO audit_logs (user_id, action, entity, entity_id, description, ip_address, user_agent) 
                    VALUES (:user_id, :action, :entity, :entity_id, :description, :ip, :ua)
                ");
                $auditStmt->execute([
                    'user_id' => $userId,
                    'action' => 'logout',
                    'entity' => 'users',
                    'entity_id' => $userId,
                    'description' => 'Cierre de sesión de usuario',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                ]);
            } catch (\Exception $e) {
                // Silenciar errores de log en logout para asegurar redirección
            }
        }

        Session::logout();
        header('Location: /');
        exit();
    }
}
