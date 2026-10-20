<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/bootstrap.php';
require_once __DIR__ . '/../app/models/AttendanceOverride.php';

header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Solo un administrador puede modificar asistencias.']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) { http_response_code(419); echo json_encode(['error' => 'Solicitud inválida.']); exit; }
$dni = trim((string) ($_POST['dni'] ?? ''));
$date = trim((string) ($_POST['date'] ?? ''));
$status = (string) ($_POST['status'] ?? '');
$observation = trim((string) ($_POST['observation'] ?? ''));
if (!preg_match('/^\d{6,15}$/', $dni) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !in_array($status, ['presente', 'tardanza', 'falta', 'justificada'], true) || $observation === '' || mb_strlen($observation) > 500) { http_response_code(422); echo json_encode(['error' => 'Datos de asistencia inválidos.']); exit; }
try { (new AttendanceOverride(db()))->save($dni, $date, $status, $observation, (int) $_SESSION['user']['id']); echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE); } catch (Throwable $exception) { http_response_code(500); echo json_encode(['error' => 'No se pudo guardar la corrección.']); }
