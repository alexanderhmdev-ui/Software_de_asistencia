<?php
declare(strict_types=1);

final class StudentDirectory
{
    public function __construct(private GoogleAttendance $attendance)
    {
    }

    public function search(string $month, string $dni, string $grade, string $section, array $allowedNames = []): array
    {
        $records = $this->attendance->search($month, $dni, $allowedNames, 100000, 0);
        $students = [];
        foreach ($records as $record) {
            if ($grade !== '' && strcasecmp($record['grade'], $grade) !== 0) continue;
            if ($section !== '' && strcasecmp($record['section'], $section) !== 0) continue;
            $key = $record['dni'];
            $students[$key] ??= ['dni' => $record['dni'], 'name' => $record['student_name'], 'grade' => $record['grade'], 'section' => $record['section'], 'total' => 0, 'present' => 0, 'late' => 0, 'absent' => 0];
            $students[$key]['total']++;
            $status = $record['attendance_status'];
            if ($status === 'presente') $students[$key]['present']++;
            elseif ($status === 'tardanza') $students[$key]['late']++;
            elseif (str_contains($status, 'falta') || str_contains($status, 'inasistencia')) $students[$key]['absent']++;
        }
        foreach ($students as &$student) {
            $student['average'] = $student['total'] ? round(($student['present'] / $student['total']) * 100, 1) : 0;
        }
        unset($student);
        usort($students, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));
        return array_values($students);
    }

    public function fromRecords(array $records, string $grade, string $section): array
    {
        $students = [];
        foreach ($records as $record) {
            if ($grade !== '' && strcasecmp($record['grade'], $grade) !== 0) continue;
            if ($section !== '' && strcasecmp($record['section'], $section) !== 0) continue;
            $key = $record['dni'];
            $students[$key] ??= ['dni' => $record['dni'], 'name' => $record['student_name'], 'grade' => $record['grade'], 'section' => $record['section'], 'total' => 0, 'present' => 0, 'late' => 0, 'absent' => 0];
            $students[$key]['total']++;
            if ($record['attendance_status'] === 'presente') $students[$key]['present']++;
            elseif ($record['attendance_status'] === 'tardanza') $students[$key]['late']++;
            elseif ($record['attendance_status'] === 'falta') $students[$key]['absent']++;
        }
        foreach ($students as &$student) $student['average'] = $student['total'] ? round(($student['present'] / $student['total']) * 100, 1) : 0;
        unset($student);
        usort($students, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));
        return array_values($students);
    }
}
