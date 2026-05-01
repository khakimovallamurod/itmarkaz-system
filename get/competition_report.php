<?php
require_once __DIR__ . '/../api/bootstrap.php';

$dateFrom = clean_input($_GET['date_from'] ?? '');
$dateTo = clean_input($_GET['date_to'] ?? '');

$whereParts = [];
$hasFrom = false;
$hasTo = false;
if ($dateFrom !== '') {
    $whereParts[] = 'c.competition_date >= ?';
    $hasFrom = true;
}
if ($dateTo !== '') {
    $whereParts[] = 'c.competition_date <= ?';
    $hasTo = true;
}
$whereSql = $whereParts ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

$safeReport = static function () use ($db, $whereSql, $hasFrom, $hasTo, $dateFrom, $dateTo): array {
    $sql = "
      SELECT
        COUNT(DISTINCT c.id) AS competitions_count,
        COUNT(DISTINCT cp.student_id) AS participants_count,
        COUNT(DISTINCT CASE WHEN crt.code = 'winner' OR cr.position IN (1,2,3) THEN cr.student_id END) AS winners_count
      FROM competitions c
      LEFT JOIN competition_participants cp ON cp.competition_id = c.id
      LEFT JOIN competition_results cr ON cr.competition_id = c.id
      LEFT JOIN competition_result_types crt ON crt.id = cr.award_type_id
      {$whereSql}
    ";
    $stmt = $db->prepare($sql);
    if ($hasFrom && $hasTo) {
        $stmt->bind_param('ss', $dateFrom, $dateTo);
    } elseif ($hasFrom) {
        $stmt->bind_param('s', $dateFrom);
    } elseif ($hasTo) {
        $stmt->bind_param('s', $dateTo);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: [];
};

$report = [];
try {
    $report = $safeReport();
} catch (Throwable $e) {
    $sql = "
      SELECT
        COUNT(DISTINCT c.id) AS competitions_count,
        COUNT(DISTINCT cp.student_id) AS participants_count,
        COUNT(DISTINCT CASE WHEN cr.position IN (1,2,3) THEN cr.student_id END) AS winners_count
      FROM competitions c
      LEFT JOIN competition_participants cp ON cp.competition_id = c.id
      LEFT JOIN competition_results cr ON cr.competition_id = c.id
      {$whereSql}
    ";
    $stmt = $db->prepare($sql);
    if ($hasFrom && $hasTo) {
        $stmt->bind_param('ss', $dateFrom, $dateTo);
    } elseif ($hasFrom) {
        $stmt->bind_param('s', $dateFrom);
    } elseif ($hasTo) {
        $stmt->bind_param('s', $dateTo);
    }
    $stmt->execute();
    $report = $stmt->get_result()->fetch_assoc() ?: [];
}

$fetchNames = static function (string $condition) use ($db): array {
    try {
        $res = $db->query("SELECT name FROM competitions WHERE {$condition} ORDER BY competition_date ASC, id ASC LIMIT 30");
        if (!$res) {
            return [];
        }
        return array_map(static fn (array $row): string => (string) ($row['name'] ?? ''), $res->fetch_all(MYSQLI_ASSOC));
    } catch (Throwable $e) {
        return [];
    }
};

$periodNames = [
    'past' => $fetchNames('competition_date < CURDATE()'),
    'upcoming_15' => $fetchNames('competition_date >= CURDATE() AND competition_date <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)'),
    'upcoming_after_15' => $fetchNames('competition_date > DATE_ADD(CURDATE(), INTERVAL 15 DAY)'),
];

json_response(true, 'Competition report', [
    'competitions_count' => (int) ($report['competitions_count'] ?? 0),
    'participants_count' => (int) ($report['participants_count'] ?? 0),
    'winners_count' => (int) ($report['winners_count'] ?? 0),
    'period_names' => $periodNames,
]);

