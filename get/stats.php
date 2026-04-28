<?php
require_once __DIR__ . '/../api/bootstrap.php';

$safeQuery = static function (string $sql) use ($db) {
    try {
        return $db->query($sql);
    } catch (Throwable $e) {
        return false;
    }
};
$safeCount = static function (string $sql, string $field = 'cnt') use ($safeQuery): int {
    $res = $safeQuery($sql);
    if (!$res) {
        return 0;
    }
    $row = $res->fetch_assoc() ?: [];
    return (int) ($row[$field] ?? 0);
};

$stats = [
    'students' => 0,
    'residents' => 0,
    'course_students' => 0,
    'mentors' => 0,
    'competitions' => 0,
    'schedule' => 0,
    'winners' => [],
    'top_students' => [],
    'result_distribution' => [
        'first' => 0,
        'second' => 0,
        'third' => 0,
    ],
    'recent_activity' => [],
    'upcoming_competitions' => [],
];

$stats['students'] = $safeCount('SELECT COUNT(*) cnt FROM students');
$stats['residents'] = $safeCount("\n  SELECT COUNT(DISTINCT s.id) cnt\n  FROM students s\n  JOIN student_status ss ON ss.student_id = s.id\n  JOIN statuses st ON st.id = ss.status_id AND LOWER(TRIM(st.name)) = LOWER('Rezident')\n");
$stats['course_students'] = $safeCount("\n  SELECT COUNT(DISTINCT s.id) cnt\n  FROM students s\n  JOIN student_status ss ON ss.student_id = s.id\n  JOIN statuses st ON st.id = ss.status_id AND LOWER(TRIM(st.name)) = LOWER('Kurs o''quvchi')\n");
$stats['mentors'] = $safeCount('SELECT COUNT(*) cnt FROM mentors');
$stats['competitions'] = $safeCount('SELECT COUNT(*) cnt FROM competitions');
$stats['schedule'] = $safeCount('SELECT COUNT(*) cnt FROM schedule');

$distRes = $safeQuery("\n  SELECT\n    SUM(CASE WHEN position = 1 THEN 1 ELSE 0 END) AS first_count,\n    SUM(CASE WHEN position = 2 THEN 1 ELSE 0 END) AS second_count,\n    SUM(CASE WHEN position = 3 THEN 1 ELSE 0 END) AS third_count\n  FROM competition_results\n");
$dist = $distRes ? ($distRes->fetch_assoc() ?: []) : [];
$stats['result_distribution'] = [
    'first' => (int) ($dist['first_count'] ?? 0),
    'second' => (int) ($dist['second_count'] ?? 0),
    'third' => (int) ($dist['third_count'] ?? 0),
];

$winnerRes = $safeQuery("\n  SELECT s.id AS student_id, s.fio, COUNT(*) AS wins\n  FROM competition_results cr\n  JOIN students s ON s.id = cr.student_id\n  WHERE cr.position = 1\n  GROUP BY s.id, s.fio\n  ORDER BY wins DESC, s.fio ASC\n  LIMIT 10\n");
$stats['winners'] = $winnerRes ? $winnerRes->fetch_all(MYSQLI_ASSOC) : [];

$topStudentsRes = $safeQuery("\n  SELECT\n    s.id AS student_id,\n    s.fio,\n    SUM(CASE\n      WHEN cr.position = 1 THEN 5\n      WHEN cr.position = 2 THEN 3\n      WHEN cr.position = 3 THEN 1\n      ELSE 0\n    END) AS points,\n    COUNT(*) AS total_results\n  FROM competition_results cr\n  JOIN students s ON s.id = cr.student_id\n  GROUP BY s.id, s.fio\n  ORDER BY points DESC, total_results DESC, s.fio ASC\n  LIMIT 10\n");
$stats['top_students'] = $topStudentsRes ? $topStudentsRes->fetch_all(MYSQLI_ASSOC) : [];

$upcomingRes = $safeQuery("\n  SELECT id, name, competition_date, location\n  FROM competitions\n  WHERE competition_date >= CURDATE()\n  ORDER BY competition_date ASC\n  LIMIT 6\n");
$stats['upcoming_competitions'] = $upcomingRes ? $upcomingRes->fetch_all(MYSQLI_ASSOC) : [];

$recentActivitySql = "\n  (SELECT 'student' AS activity_type, CONCAT('Yangi talaba: ', fio) AS title, created_at FROM students)\n  UNION ALL\n  (SELECT 'competition' AS activity_type, CONCAT('Tanlov yaratildi: ', name) AS title, created_at FROM competitions)\n  UNION ALL\n  (SELECT 'result' AS activity_type, CONCAT('Natija kiritildi: ', s.fio, ' - ', c.name) AS title, cr.created_at\n   FROM competition_results cr\n   JOIN students s ON s.id = cr.student_id\n   JOIN competitions c ON c.id = cr.competition_id)\n  UNION ALL\n  (SELECT 'team' AS activity_type, CONCAT('Jamoa yaratildi: ', team_name) AS title, created_at FROM teams)\n  ORDER BY created_at DESC\n  LIMIT 8\n";
$recentRes = $safeQuery($recentActivitySql);
$stats['recent_activity'] = $recentRes ? $recentRes->fetch_all(MYSQLI_ASSOC) : [];

json_response(true, 'Statistika olindi.', $stats);
