<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/bootstrap.php';
require_once __DIR__ . '/../app/models/Post.php';
require_auth();
$user = $_SESSION['user'];
if (!in_array($user['role'], ['admin', 'docente'], true)) { http_response_code(403); exit('No tienes permiso para publicar.'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) $errors[] = 'Solicitud inválida.';
$title = trim((string) ($_POST['title'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$type = (string) ($_POST['type'] ?? 'aviso');
if ($title === '' || mb_strlen($title) > 150) $errors[] = 'El título es obligatorio y no debe superar 150 caracteres.';
if ($description === '' || mb_strlen($description) > 5000) $errors[] = 'La descripción es obligatoria.';
if (!in_array($type, ['app', 'evento', 'aviso'], true)) $errors[] = 'Tipo de publicación inválido.';
$filePath = null; $imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK || $_FILES['image']['size'] > 15 * 1024 * 1024) $errors[] = 'La imagen debe pesar menos de 15 MB.';
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['image']['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) $errors[] = 'Solo se permiten imágenes JPG, PNG o WEBP.';
    if (!$errors) {
        $directory = __DIR__ . '/uploads'; if (!is_dir($directory)) mkdir($directory, 0755, true);
        $fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        move_uploaded_file($_FILES['image']['tmp_name'], $directory . '/' . $fileName);
        $imagePath = 'uploads/' . $fileName;
    }
}
if (!$errors) { (new Post(db()))->create((int) $user['id'], $title, $description, $type, $filePath, $imagePath); redirect('index.php?view=publicaciones&created=1'); }
$_SESSION['post_errors'] = $errors; redirect('index.php?view=publicaciones');
