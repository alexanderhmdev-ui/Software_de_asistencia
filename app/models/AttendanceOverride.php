<?php
declare(strict_types=1);

final class AttendanceOverride
{
    public function __construct(private PDO $database)
    {
        $this->database->exec("CREATE TABLE IF NOT EXISTS attendance_overrides (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            dni VARCHAR(15) NOT NULL,
            attendance_date DATE NOT NULL,
            status ENUM('presente', 'tardanza', 'falta', 'justificada') NOT NULL,
            observation VARCHAR(500) NOT NULL,
            edited_by INT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_attendance_override (dni, attendance_date),
            INDEX idx_attendance_override_date (attendance_date)
        ) ENGINE=InnoDB");
    }

    public function apply(array $records): array
    {
        if (!$records) return $records;
        $dnis = array_values(array_unique(array_column($records, 'dni')));
        $placeholders = implode(',', array_fill(0, count($dnis), '?'));
        $statement = $this->database->prepare("SELECT dni, attendance_date, status, observation FROM attendance_overrides WHERE dni IN ($placeholders)");
        $statement->execute($dnis);
        $overrides = [];
        foreach ($statement->fetchAll() as $override) $overrides[$override['dni'] . '|' . $override['attendance_date']] = $override;
        foreach ($records as &$record) {
            $key = $record['dni'] . '|' . substr($record['fecha'], 0, 10);
            if (isset($overrides[$key])) {
                $record['status'] = $overrides[$key]['status'];
                $record['attendance_status'] = $overrides[$key]['status'] === 'justificada' ? 'presente' : $overrides[$key]['status'];
                $record['observation'] = $overrides[$key]['observation'];
                $record['edited'] = true;
            }
        }
        unset($record);
        return $records;
    }

    public function save(string $dni, string $date, string $status, string $observation, int $userId): void
    {
        $statement = $this->database->prepare("INSERT INTO attendance_overrides (dni, attendance_date, status, observation, edited_by) VALUES (:dni, :attendance_date, :status, :observation, :edited_by) ON DUPLICATE KEY UPDATE status = VALUES(status), observation = VALUES(observation), edited_by = VALUES(edited_by)");
        $statement->execute(['dni' => $dni, 'attendance_date' => $date, 'status' => $status, 'observation' => $observation, 'edited_by' => $userId]);
    }
}
