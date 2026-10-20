<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/bootstrap.php';
require_once __DIR__ . '/../app/models/Dashboard.php';
require_once __DIR__ . '/../app/models/GoogleAttendance.php';
require_once __DIR__ . '/../app/models/AttendanceOverride.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no válida.']);
    exit;
}

$user = $_SESSION['user'];
$month = (string) ($_GET['month'] ?? date('Y-m'));
$search = trim((string) ($_GET['search'] ?? ''));
$offset = max(0, (int) ($_GET['offset'] ?? 0));
$limit = 100;

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    http_response_code(422);
    echo json_encode(['error' => 'Mes inválido.']);
    exit;
}

try {
    $dashboard = new Dashboard(db());
    $children = $dashboard->children((int) $user['id'], (string) $user['role']);
    $allowedNames = $user['role'] === 'padre' ? array_column($children, 'name') : [];
    $records = (new GoogleAttendance(GOOGLE_SHEET_ID, GOOGLE_SHEET_GID))->search($month, $search, $allowedNames, $limit, $offset);
    $records = (new AttendanceOverride(db()))->apply($records);
    echo json_encode(['source' => 'Google Sheets', 'records' => $records, 'has_more' => count($records) === $limit, 'can_edit' => $user['role'] === 'admin'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(503);
    echo json_encode(['error' => 'No se pudo consultar la asistencia.']);
}
