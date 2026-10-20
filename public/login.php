<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/bootstrap.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

try {
	(new AuthController(new User(db())))->login();
} catch (PDOException $exception) {
	error_log($exception->getMessage());
	$errors = ['No se pudo conectar con la base de datos. Revisa el archivo .env y que MySQL esté iniciado.'];
	$old = ['dni' => trim((string) ($_POST['dni'] ?? ''))];
	require __DIR__ . '/../app/views/login.php';
}
