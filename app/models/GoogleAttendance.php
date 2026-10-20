<?php
declare(strict_types=1);

final class GoogleAttendance
{
    private const CACHE_TTL = 60;

    public function __construct(private string $spreadsheetId, private string $gid)
    {
    }

    public function search(string $month, string $search, array $allowedNames = [], int $limit = 100, int $offset = 0): array
    {
        if (trim($this->spreadsheetId) === '') {
            throw new RuntimeException('Google Sheets no está configurado.');
        }

        $url = sprintf(
            'https://docs.google.com/spreadsheets/d/%s/export?format=csv&gid=%s',
            rawurlencode($this->spreadsheetId),
            rawurlencode($this->gid)
        );
        $handle = $this->openCsv($url);
        $records = [];
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return [];
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 7) {
                continue;
            }
            $date = substr(trim($row[4]), 0, 16);
            $name = trim($row[1]);
            $dni = trim($row[0]);
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date) || !str_starts_with($date, $month)) {
                continue;
            }
            if ($allowedNames && !$this->matchesAllowedName($name, $allowedNames)) {
                continue;
            }
            if ($search !== '' && stripos($dni . ' ' . $name, $search) === false) {
                continue;
            }
            $observation = trim($row[5]);
            $status = $observation;
            $attendanceStatus = mb_strtolower($observation) === 'ninguno' ? 'presente' : mb_strtolower($observation);
            if ($this->isAfterAttendanceCutoff($date) && mb_strtolower($observation) === 'ninguno') {
                $status = 'falta automática';
                $attendanceStatus = 'falta';
                $observation = 'Inasistencia automática después de las 08:45.';
            }
            $records[] = ['dni' => $dni, 'student_name' => $name, 'grade' => trim($row[2]), 'section' => trim($row[3]), 'fecha' => $date, 'status' => $status, 'attendance_status' => $attendanceStatus, 'observation' => $observation, 'registrador' => trim($row[6])];
        }
        fclose($handle);
        usort($records, static fn (array $a, array $b): int => strcmp($b['fecha'], $a['fecha']));
        return array_slice($records, $offset, $limit);
    }

    private function openCsv(string $url)
    {
        $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'simon-attendance-' . sha1($url) . '.csv';
        if (is_file($cacheFile) && time() - filemtime($cacheFile) < self::CACHE_TTL) {
            $content = file_get_contents($cacheFile);
        } else {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_USERAGENT => 'SimonBolivarDashboard/1.0',
            ]);
            $content = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($content !== false && $status < 400) {
                file_put_contents($cacheFile, $content, LOCK_EX);
            }
        }
        if ($content === false || $content === null || $content === '') {
            throw new RuntimeException('No se pudo consultar Google Sheets.');
        }
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        return $stream;
    }

    private function matchesAllowedName(string $name, array $allowedNames): bool
    {
        foreach ($allowedNames as $allowedName) {
            if (mb_strtolower(trim($name)) === mb_strtolower(trim($allowedName))) return true;
        }
        return false;
    }

    private function isAfterAttendanceCutoff(string $date): bool
    {
        return substr($date, 11, 5) > '08:45';
    }
}
