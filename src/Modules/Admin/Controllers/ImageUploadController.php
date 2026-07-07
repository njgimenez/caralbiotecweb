<?php

namespace Caral\Modules\Admin\Controllers;

use Caral\Core\Session;

class ImageUploadController
{
    private string $uploadDir;
    private string $uploadUrl = '/uploads/';
    private int $maxSizeBytes = 5 * 1024 * 1024; // 5 MB
    private array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    private array $allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];

    public function __construct()
    {
        $this->uploadDir = dirname(__DIR__, 4) . '/public/uploads/';
    }

    private function requireAdmin(): void
    {
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado.']);
            exit();
        }
        $role = Session::getUserRole();
        if (!in_array($role, ['Super Administrador', 'Marketing', 'Operaciones'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Sin permisos.']);
            exit();
        }
    }

    /**
     * POST /admin/upload-imagen
     * Accepts either:
     *   - multipart field "image_file" (raw file upload)
     *   - JSON body { "image_data": "data:image/png;base64,..." }
     */
    public function upload(): void
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // ── Mode 1: base64 canvas blob (from Cropper.js) ──────────────────
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $body = json_decode(file_get_contents('php://input'), true);
            $dataUrl = $body['image_data'] ?? '';

            if (!preg_match('/^data:(image\/[a-z]+);base64,(.+)$/i', $dataUrl, $m)) {
                http_response_code(400);
                echo json_encode(['error' => 'Formato de imagen no válido.']);
                return;
            }

            $mime   = strtolower($m[1]);
            $binary = base64_decode($m[2]);

            if (!in_array($mime, $this->allowedMimes)) {
                http_response_code(415);
                echo json_encode(['error' => 'Tipo de imagen no permitido.']);
                return;
            }

            if (strlen($binary) > $this->maxSizeBytes) {
                http_response_code(413);
                echo json_encode(['error' => 'La imagen supera el límite de 5 MB.']);
                return;
            }

            $ext      = str_replace('image/', '', $mime);
            $ext      = ($ext === 'jpeg') ? 'jpg' : $ext;
            $filename = 'prod_' . uniqid() . '.' . $ext;
            $path     = $this->uploadDir . $filename;

            if (file_put_contents($path, $binary) === false) {
                http_response_code(500);
                echo json_encode(['error' => 'No se pudo guardar la imagen.']);
                return;
            }

            echo json_encode(['url' => $this->uploadUrl . $filename]);
            return;
        }

        // ── Mode 2: multipart file upload ──────────────────────────────────
        if (empty($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
            $err = $_FILES['image_file']['error'] ?? -1;
            $msg = match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño permitido.',
                UPLOAD_ERR_NO_FILE  => 'No se recibió ningún archivo.',
                default             => 'Error al subir el archivo.',
            };
            http_response_code(400);
            echo json_encode(['error' => $msg]);
            return;
        }

        $file = $_FILES['image_file'];

        if ($file['size'] > $this->maxSizeBytes) {
            http_response_code(413);
            echo json_encode(['error' => 'La imagen supera el límite de 5 MB.']);
            return;
        }

        // Validate MIME using finfo (more reliable than the browser-reported type)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $this->allowedMimes)) {
            http_response_code(415);
            echo json_encode(['error' => 'Solo se permiten imágenes JPG, PNG o WebP.']);
            return;
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $filename = 'prod_' . uniqid() . '.' . $ext;
        $dest     = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar la imagen en el servidor.']);
            return;
        }

        echo json_encode(['url' => $this->uploadUrl . $filename]);
    }
}
