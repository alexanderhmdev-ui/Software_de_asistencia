<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/bootstrap.php';
require_once __DIR__ . '/../app/models/Dashboard.php';
require_once __DIR__ . '/../app/models/GoogleAttendance.php';
require_once __DIR__ . '/../app/models/StudentDirectory.php';
require_once __DIR__ . '/../app/models/AttendanceOverride.php';

header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['error' => 'Sesión no válida.']); exit; }
$user = $_SESSION['user'];
$month = (string) ($_GET['month'] ?? date('Y-m'));
$dni = trim((string) ($_GET['dni'] ?? ''));
$grade = trim((string) ($_GET['grade'] ?? ''));
$section = trim((string) ($_GET['section'] ?? ''));
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) { http_response_code(422); echo json_encode(['error' => 'Mes inválido.']); exit; }
try {
    $googleAttendance = new GoogleAttendance(GOOGLE_SHEET_ID, GOOGLE_SHEET_GID);
    $allowedNames = [];
    if ($user['role'] === 'padre') {
        $children = (new Dashboard(db()))->children((int) $user['id'], 'padre');
        $allowedNames = array_column($children, 'name');
    }
    $records = $googleAttendance->search($month, $dni, $allowedNames, 100000, 0);
    $records = (new AttendanceOverride(db()))->apply($records);
    $students = (new StudentDirectory($googleAttendance))->fromRecords($records, $grade, $section);
    echo json_encode(['students' => $students, 'month' => $month], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(503); echo json_encode(['error' => 'No se pudo consultar estudiantes.']);
}
