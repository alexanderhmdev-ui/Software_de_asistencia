<?php
declare(strict_types=1);

final class Dashboard
{
    public function __construct(private PDO $database)
    {
    }

    public function summary(int $userId, string $role): array
    {
        $studentFilter = $role === 'padre' ? ' WHERE parent_id = :user_id' : '';
        $studentParams = $role === 'padre' ? ['user_id' => $userId] : [];

        $students = $this->database->prepare('SELECT COUNT(*) FROM students' . $studentFilter);
        $students->execute($studentParams);

        $users = $this->database->query('SELECT COUNT(*) FROM users');
        $linkedParents = $this->database->query('SELECT COUNT(DISTINCT parent_id) FROM students WHERE parent_id IS NOT NULL');
        $teachers = $this->database->query("SELECT COUNT(*) FROM users WHERE role = 'docente'");
        $posts = $this->database->query('SELECT COUNT(*) FROM posts');

        $attendanceSql = "SELECT
                SUM(status = 'presente') AS present,
                SUM(status = 'tardanza') AS late,
                SUM(status = 'falta') AS absent
            FROM asistencia a";
        if ($role === 'padre') {
            $attendanceSql .= ' INNER JOIN students s ON s.id = a.student_id WHERE s.parent_id = :user_id AND a.fecha = CURDATE()';
            $attendance = $this->database->prepare($attendanceSql);
            $attendance->execute(['user_id' => $userId]);
        } else {
            $attendanceSql .= ' WHERE a.fecha = CURDATE()';
            $attendance = $this->database->query($attendanceSql);
        }

        $attendanceData = $attendance->fetch() ?: [];

        return [
            'students' => (int) $students->fetchColumn(),
            'users' => (int) $users->fetchColumn(),
            'linked_parents' => (int) $linkedParents->fetchColumn(),
            'teachers' => (int) $teachers->fetchColumn(),
            'posts' => (int) $posts->fetchColumn(),
            'present' => (int) ($attendanceData['present'] ?? 0),
            'late' => (int) ($attendanceData['late'] ?? 0),
            'absent' => (int) ($attendanceData['absent'] ?? 0),
        ];
    }

    public function children(int $userId, string $role): array
    {
        if ($role !== 'padre') {
            return [];
        }

        $statement = $this->database->prepare(
            "SELECT s.id, s.name, s.grade, s.section,
                COALESCE(a.status, 'sin registro') AS today_status
             FROM students s
             LEFT JOIN asistencia a ON a.student_id = s.id AND a.fecha = CURDATE()
             WHERE s.parent_id = :user_id
             ORDER BY s.name"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function recentPosts(): array
    {
        $statement = $this->database->query(
            "SELECT p.id, p.title, p.descripcion, p.tye AS type, p.image_patch, p.created_at,
                u.name AS author, COUNT(l.id) AS likes
             FROM posts p
             INNER JOIN users u ON u.id_user = p.user_id
             LEFT JOIN likes l ON l.post_id = p.id
             GROUP BY p.id, p.title, p.descripcion, p.tye, p.image_patch, p.created_at, u.name
             ORDER BY p.created_at DESC
             LIMIT 5"
        );

        return $statement->fetchAll();
    }

    public function monthlyAttendance(int $userId, string $role, string $month): array
    {
        $sql = "SELECT a.fecha, a.status, s.name AS student_name
                FROM asistencia a
                INNER JOIN students s ON s.id = a.student_id
                WHERE DATE_FORMAT(a.fecha, '%Y-%m') = :month";
        $params = ['month' => $month];

        if ($role === 'padre') {
            $sql .= ' AND s.parent_id = :user_id';
            $params['user_id'] = $userId;
        }

        $sql .= ' ORDER BY a.fecha DESC, s.name';
        $statement = $this->database->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }
}
