<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Dashboard.php';
require_once __DIR__ . '/../models/GoogleAttendance.php';

final class DashboardController
{
    public function __construct(private Dashboard $dashboard, private GoogleAttendance $googleAttendance)
    {
    }

    public function index(array $user): void
    {
        $view = in_array($_GET['view'] ?? 'inicio', ['inicio', 'publicaciones', 'asistencia', 'estudiantes', 'conocenos'], true)
            ? (string) ($_GET['view'] ?? 'inicio')
            : 'inicio';
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) ($_GET['month'] ?? ''))
            ? (string) $_GET['month']
            : date('Y-m');
        $summary = $this->dashboard->summary((int) $user['id'], (string) $user['role']);
        $children = $this->dashboard->children((int) $user['id'], (string) $user['role']);
        $posts = $this->dashboard->recentPosts();
        $attendance = [];
        $attendanceSource = 'MySQL';
        $postErrors = $_SESSION['post_errors'] ?? [];
        unset($_SESSION['post_errors']);
        $search = trim((string) ($_GET['search'] ?? ''));
        if ($view === 'asistencia' && $search !== '') {
            try {
                $allowedNames = $user['role'] === 'padre' ? array_column($children, 'name') : [];
                $attendance = $this->googleAttendance->search($month, $search, $allowedNames);
                $attendanceSource = 'Google Sheets';
            } catch (RuntimeException $exception) {
                $attendance = $this->dashboard->monthlyAttendance((int) $user['id'], (string) $user['role'], $month);
            }
        }

        require __DIR__ . '/../views/dashboard.php';
    }
}
