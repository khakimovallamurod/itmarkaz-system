<?php

function qp_int(array $input, string $key, int $default = 1, int $min = 1): int
{
    $value = isset($input[$key]) ? (int) $input[$key] : $default;
    return max($min, $value);
}

function qp_str(array $input, string $key, string $default = ''): string
{
    return trim((string) ($input[$key] ?? $default));
}

function paginate_meta(int $total, int $page, int $perPage): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $page), $pages);
    return [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'pages' => $pages,
        'offset' => ($page - 1) * $perPage,
    ];
}

function fetch_stats_data(mysqli $db): array
{
    $safe = static function (string $sql) use ($db) {
        try {
            return $db->query($sql);
        } catch (Throwable $e) {
            return false;
        }
    };
    $count = static function (string $sql, string $field = 'cnt') use ($safe): int {
        $res = $safe($sql);
        if (!$res) return 0;
        $row = $res->fetch_assoc() ?: [];
        return (int) ($row[$field] ?? 0);
    };

    $stats = [
        'students' => $count('SELECT COUNT(*) cnt FROM students'),
        'residents' => $count("SELECT COUNT(DISTINCT s.id) cnt FROM students s JOIN student_status ss ON ss.student_id=s.id JOIN statuses st ON st.id=ss.status_id AND LOWER(TRIM(st.name))=LOWER('Rezident')"),
        'course_students' => $count("SELECT COUNT(DISTINCT s.id) cnt FROM students s JOIN student_status ss ON ss.student_id=s.id JOIN statuses st ON st.id=ss.status_id AND LOWER(TRIM(st.name))=LOWER('Kurs o''quvchi')"),
        'mentors' => $count('SELECT COUNT(*) cnt FROM mentors'),
        'competitions' => $count('SELECT COUNT(*) cnt FROM competitions'),
        'schedule' => $count('SELECT COUNT(*) cnt FROM schedule'),
        'result_distribution' => ['first' => 0, 'second' => 0, 'third' => 0],
        'winners' => [],
        'top_students' => [],
        'recent_activity' => [],
        'upcoming_competitions' => [],
    ];

    $distRes = $safe("SELECT SUM(CASE WHEN position=1 THEN 1 ELSE 0 END) first_count, SUM(CASE WHEN position=2 THEN 1 ELSE 0 END) second_count, SUM(CASE WHEN position=3 THEN 1 ELSE 0 END) third_count FROM competition_results");
    if ($distRes) {
        $dist = $distRes->fetch_assoc() ?: [];
        $stats['result_distribution'] = [
            'first' => (int) ($dist['first_count'] ?? 0),
            'second' => (int) ($dist['second_count'] ?? 0),
            'third' => (int) ($dist['third_count'] ?? 0),
        ];
    }

    $winnerRes = $safe("SELECT s.id AS student_id, s.fio, COUNT(*) AS wins FROM competition_results cr JOIN students s ON s.id=cr.student_id WHERE cr.position=1 GROUP BY s.id, s.fio ORDER BY wins DESC, s.fio ASC LIMIT 10");
    if ($winnerRes) $stats['winners'] = $winnerRes->fetch_all(MYSQLI_ASSOC);

    $topRes = $safe("SELECT s.id AS student_id, s.fio, SUM(CASE WHEN cr.position=1 THEN 5 WHEN cr.position=2 THEN 3 WHEN cr.position=3 THEN 1 ELSE 0 END) AS points, COUNT(*) AS total_results FROM competition_results cr JOIN students s ON s.id=cr.student_id GROUP BY s.id, s.fio ORDER BY points DESC, total_results DESC, s.fio ASC LIMIT 10");
    if ($topRes) $stats['top_students'] = $topRes->fetch_all(MYSQLI_ASSOC);

    $upcomingRes = $safe("SELECT id, name, competition_date, location FROM competitions WHERE competition_date >= CURDATE() ORDER BY competition_date ASC LIMIT 6");
    if ($upcomingRes) $stats['upcoming_competitions'] = $upcomingRes->fetch_all(MYSQLI_ASSOC);

    $recentSql = "
      (SELECT 'student' activity_type, CONCAT('Yangi talaba: ', fio) AS title, created_at FROM students)
      UNION ALL
      (SELECT 'competition' activity_type, CONCAT('Tanlov yaratildi: ', name) AS title, created_at FROM competitions)
      UNION ALL
      (SELECT 'result' activity_type, CONCAT('Natija kiritildi: ', s.fio, ' - ', c.name) AS title, cr.created_at
       FROM competition_results cr
       JOIN students s ON s.id = cr.student_id
       JOIN competitions c ON c.id = cr.competition_id)
      UNION ALL
      (SELECT 'team' activity_type, CONCAT('Jamoa yaratildi: ', team_name) AS title, created_at FROM teams)
      ORDER BY created_at DESC
      LIMIT 8
    ";
    $recentRes = $safe($recentSql);
    if ($recentRes) $stats['recent_activity'] = $recentRes->fetch_all(MYSQLI_ASSOC);

    return $stats;
}

function load_page_options(mysqli $db, string $page): array
{
    $options = [];

    $needsDirections = in_array($page, ['students'], true);
    $needsStatuses = in_array($page, ['students'], true);
    $needsRooms = in_array($page, ['residents', 'course_students'], true);
    $needsCourses = in_array($page, ['course_students', 'mentors'], true);
    $needsResidentStudents = in_array($page, ['mentors', 'residents'], true);
    $needsCourseStudentOptions = in_array($page, ['course_students'], true);
    $needsCompetitionResultTypes = in_array($page, ['competition_detail'], true);
    $needsStudentOptions = in_array($page, ['teams', 'projects', 'competition_detail'], true);
    $needsWeekDays = in_array($page, ['courses'], true);

    if ($needsDirections) {
        $res = $db->query('SELECT id, name FROM directions ORDER BY name ASC');
        $options['directions'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    if ($needsStatuses) {
        $res = $db->query('SELECT id, name FROM statuses ORDER BY name ASC');
        $options['statuses'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    if ($needsRooms) {
        $res = $db->query('SELECT id, room_number FROM rooms ORDER BY room_number ASC');
        $options['rooms'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    if ($needsCourses) {
        $res = $db->query('SELECT id, name FROM courses ORDER BY name ASC');
        $options['courses'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    if ($needsResidentStudents) {
        if ($page === 'mentors') {
            $sql = "
              SELECT DISTINCT s.id, s.fio
              FROM students s
              JOIN student_status ss ON ss.student_id = s.id
              JOIN statuses st ON st.id = ss.status_id
              WHERE LOWER(TRIM(st.name)) = LOWER('Rezident')
              ORDER BY s.fio ASC
            ";
        } else {
            $sql = "
              SELECT s.id, s.fio, CASE WHEN r.id IS NULL THEN 0 ELSE 1 END AS is_assigned
              FROM students s
              LEFT JOIN residents r ON r.student_id = s.id
              ORDER BY s.fio ASC
            ";
        }
        $res = $db->query($sql);
        $options['resident_students'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    if ($needsCourseStudentOptions) {
        $sql = "
          SELECT s.id, s.fio, CASE WHEN csm.student_id IS NULL THEN 0 ELSE 1 END AS is_assigned
          FROM students s
          LEFT JOIN (
            SELECT DISTINCT student_id
            FROM course_students
          ) csm ON csm.student_id = s.id
          ORDER BY s.fio ASC
        ";
        $res = $db->query($sql);
        $options['course_students_options'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    if ($needsCompetitionResultTypes) {
        try {
            $res = $db->query('SELECT id, code, name FROM competition_result_types ORDER BY sort_order ASC, id ASC');
            $options['competition_result_types'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Throwable $e) {
            $options['competition_result_types'] = [];
        }
    }
    if ($needsStudentOptions) {
        $res = $db->query('SELECT id, fio FROM students ORDER BY fio ASC');
        $options['student_options'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    if ($needsWeekDays) {
        $res = $db->query('SELECT id, code, name FROM week_days ORDER BY id ASC');
        $options['week_days'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    return $options;
}

function load_page_data(mysqli $db, string $page, array $input): array
{
    if ($page === 'dashboard' || $page === 'statistics') {
        return ['stats' => fetch_stats_data($db)];
    }

    if ($page === 'students') {
        $search = qp_str($input, 'search');
        $directionId = qp_int($input, 'direction_id', 0, 0);
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 10;

        $q = '%' . $search . '%';
        $countSql = "SELECT COUNT(*) cnt FROM students s JOIN directions d ON d.id=s.yonalish_id WHERE (s.fio LIKE ? OR d.name LIKE ? OR s.guruh LIKE ?)" . ($directionId > 0 ? ' AND s.yonalish_id=?' : '');
        $countStmt = $db->prepare($countSql);
        if ($directionId > 0) {
            $countStmt->bind_param('sssi', $q, $q, $q, $directionId);
        } else {
            $countStmt->bind_param('sss', $q, $q, $q);
        }
        $countStmt->execute();
        $total = (int) (($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0));
        $meta = paginate_meta($total, $pageNum, $perPage);

        $sql = "
          SELECT
            s.id, s.fio, s.yonalish_id, d.name AS yonalish, s.guruh, s.kirgan_yili, s.telefon, s.telegram_chat_id,
            GROUP_CONCAT(DISTINCT st.id ORDER BY st.id SEPARATOR '||') AS status_ids_raw,
            GROUP_CONCAT(DISTINCT st.name ORDER BY st.name SEPARATOR '||') AS status_names
          FROM students s
          JOIN directions d ON d.id=s.yonalish_id
          LEFT JOIN student_status ss ON ss.student_id=s.id
          LEFT JOIN statuses st ON st.id=ss.status_id
          WHERE (s.fio LIKE ? OR d.name LIKE ? OR s.guruh LIKE ?)
          " . ($directionId > 0 ? ' AND s.yonalish_id=? ' : '') . "
          GROUP BY s.id, s.fio, s.yonalish_id, d.name, s.guruh, s.kirgan_yili, s.telefon, s.telegram_chat_id
          ORDER BY s.id DESC
          LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        if ($directionId > 0) {
            $stmt->bind_param('sssiii', $q, $q, $q, $directionId, $meta['per_page'], $meta['offset']);
        } else {
            $stmt->bind_param('sssii', $q, $q, $q, $meta['per_page'], $meta['offset']);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as &$row) {
            $row['statuses'] = trim((string) ($row['status_names'] ?? '')) === '' ? [] : explode('||', (string) $row['status_names']);
            $row['status_ids'] = trim((string) ($row['status_ids_raw'] ?? '')) === '' ? [] : array_map('intval', explode('||', (string) $row['status_ids_raw']));
        }
        unset($row);

        return ['items' => $rows, 'pagination' => $meta, 'filters' => ['search' => $search, 'direction_id' => $directionId]];
    }

    if ($page === 'residents') {
        $search = qp_str($input, 'search');
        $status = qp_str($input, 'status');
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 10;

        $q = '%' . $search . '%';
        $statusSql = '';
        if ($status === 'assigned') $statusSql = ' AND r.id IS NOT NULL ';
        if ($status === 'unassigned') $statusSql = ' AND r.id IS NULL ';

        $countSql = "
          SELECT COUNT(DISTINCT s.id) cnt
          FROM students s
          LEFT JOIN residents r ON r.student_id=s.id
          WHERE s.fio LIKE ? {$statusSql}
        ";
        $countStmt = $db->prepare($countSql);
        $countStmt->bind_param('s', $q);
        $countStmt->execute();
        $total = (int) (($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0));
        $meta = paginate_meta($total, $pageNum, $perPage);

        $sql = "
          SELECT DISTINCT r.id, s.id AS student_id, s.fio, rm.id AS room_id, rm.room_number, r.computer_number
          FROM students s
          LEFT JOIN residents r ON r.student_id=s.id
          LEFT JOIN rooms rm ON rm.id=r.room_id
          WHERE s.fio LIKE ? {$statusSql}
          ORDER BY s.fio ASC
          LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sii', $q, $meta['per_page'], $meta['offset']);
        $stmt->execute();
        return ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => $meta, 'filters' => ['search' => $search, 'status' => $status]];
    }

    if ($page === 'course_students') {
        $search = qp_str($input, 'search');
        $status = qp_str($input, 'status');
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 10;

        $q = '%' . $search . '%';
        $statusSql = in_array($status, ['active', 'completed'], true) ? ' AND cs.status = ? ' : '';

        $countSql = "
          SELECT COUNT(DISTINCT s.id) cnt
          FROM students s
          LEFT JOIN course_students cs ON cs.student_id=s.id
          WHERE s.fio LIKE ? {$statusSql}
        ";
        $countStmt = $db->prepare($countSql);
        if ($statusSql !== '') {
            $countStmt->bind_param('ss', $q, $status);
        } else {
            $countStmt->bind_param('s', $q);
        }
        $countStmt->execute();
        $total = (int) (($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0));
        $meta = paginate_meta($total, $pageNum, $perPage);

        $sql = "
          SELECT DISTINCT s.id AS student_id, s.fio, cs.id, cs.course_id, c.name AS course_name, cs.room_id, r.room_number, cs.status
          FROM students s
          LEFT JOIN course_students cs ON cs.student_id=s.id
          LEFT JOIN courses c ON c.id=cs.course_id
          LEFT JOIN rooms r ON r.id=cs.room_id
          WHERE s.fio LIKE ? {$statusSql}
          ORDER BY s.fio ASC
          LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        if ($statusSql !== '') {
            $stmt->bind_param('ssii', $q, $status, $meta['per_page'], $meta['offset']);
        } else {
            $stmt->bind_param('sii', $q, $meta['per_page'], $meta['offset']);
        }
        $stmt->execute();
        return ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => $meta, 'filters' => ['search' => $search, 'status' => $status]];
    }

    if ($page === 'rooms') {
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 10;
        $totalRes = $db->query('SELECT COUNT(*) cnt FROM rooms');
        $total = (int) (($totalRes ? $totalRes->fetch_assoc()['cnt'] : 0) ?? 0);
        $meta = paginate_meta($total, $pageNum, $perPage);
        $stmt = $db->prepare('SELECT id, room_number, capacity, computers_count FROM rooms ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        $stmt->execute();
        return ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => $meta];
    }

    if ($page === 'courses') {
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 10;
        $totalRes = $db->query('SELECT COUNT(*) cnt FROM courses');
        $total = (int) (($totalRes ? $totalRes->fetch_assoc()['cnt'] : 0) ?? 0);
        $meta = paginate_meta($total, $pageNum, $perPage);
        $stmt = $db->prepare('SELECT id, name, description, days, time, duration FROM courses ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        $stmt->execute();
        return ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => $meta];
    }

    if ($page === 'mentors') {
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 10;
        $totalRes = $db->query('SELECT COUNT(*) cnt FROM mentors');
        $total = (int) (($totalRes ? $totalRes->fetch_assoc()['cnt'] : 0) ?? 0);
        $meta = paginate_meta($total, $pageNum, $perPage);
        $stmt = $db->prepare('SELECT m.id, m.student_id, s.fio, c.id AS course_id, c.name AS course_name FROM mentors m JOIN students s ON s.id=m.student_id JOIN courses c ON c.id=m.course_id ORDER BY m.id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        $stmt->execute();
        return ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => $meta];
    }

    if ($page === 'competitions') {
        $dateFrom = qp_str($input, 'date_from');
        $dateTo = qp_str($input, 'date_to');
        $period = qp_str($input, 'period');
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 9;

        $whereParts = [];
        $hasFrom = false;
        $hasTo = false;

        if ($period === 'past') {
            $whereParts[] = 'c.competition_date < CURDATE()';
        } elseif ($period === 'upcoming_15') {
            $whereParts[] = 'c.competition_date >= CURDATE() AND c.competition_date <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)';
        } elseif ($period === 'upcoming_after_15') {
            $whereParts[] = 'c.competition_date > DATE_ADD(CURDATE(), INTERVAL 15 DAY)';
        } else {
            if ($dateFrom !== '') {
                $whereParts[] = 'c.competition_date >= ?';
                $hasFrom = true;
            }
            if ($dateTo !== '') {
                $whereParts[] = 'c.competition_date <= ?';
                $hasTo = true;
            }
        }

        $whereSql = $whereParts ? (' WHERE ' . implode(' AND ', $whereParts)) : '';
        $countStmt = $db->prepare("SELECT COUNT(*) cnt FROM competitions c {$whereSql}");
        if ($hasFrom && $hasTo) {
            $countStmt->bind_param('ss', $dateFrom, $dateTo);
        } elseif ($hasFrom) {
            $countStmt->bind_param('s', $dateFrom);
        } elseif ($hasTo) {
            $countStmt->bind_param('s', $dateTo);
        }
        $countStmt->execute();
        $total = (int) (($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0));
        $meta = paginate_meta($total, $pageNum, $perPage);

        $sql = "
          SELECT
            c.id, c.name, c.description, c.registration_deadline, c.competition_date, c.location,
            (SELECT COUNT(*) FROM competition_participants cp WHERE cp.competition_id=c.id) AS participant_count,
            (SELECT COUNT(*) FROM competition_results cr WHERE cr.competition_id=c.id) AS result_count
          FROM competitions c
          {$whereSql}
          ORDER BY c.competition_date DESC, c.id DESC
          LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        if ($hasFrom && $hasTo) {
            $stmt->bind_param('ssii', $dateFrom, $dateTo, $meta['per_page'], $meta['offset']);
        } elseif ($hasFrom) {
            $stmt->bind_param('sii', $dateFrom, $meta['per_page'], $meta['offset']);
        } elseif ($hasTo) {
            $stmt->bind_param('sii', $dateTo, $meta['per_page'], $meta['offset']);
        } else {
            $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        }
        $stmt->execute();

        $report = [];
        try {
            $reportSql = "
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
            $reportStmt = $db->prepare($reportSql);
            if ($hasFrom && $hasTo) {
                $reportStmt->bind_param('ss', $dateFrom, $dateTo);
            } elseif ($hasFrom) {
                $reportStmt->bind_param('s', $dateFrom);
            } elseif ($hasTo) {
                $reportStmt->bind_param('s', $dateTo);
            }
            $reportStmt->execute();
            $report = $reportStmt->get_result()->fetch_assoc() ?: [];
        } catch (Throwable $e) {
            try {
                $fallbackReportSql = "
                  SELECT
                    COUNT(DISTINCT c.id) AS competitions_count,
                    COUNT(DISTINCT cp.student_id) AS participants_count,
                    COUNT(DISTINCT CASE WHEN cr.position IN (1,2,3) THEN cr.student_id END) AS winners_count
                  FROM competitions c
                  LEFT JOIN competition_participants cp ON cp.competition_id = c.id
                  LEFT JOIN competition_results cr ON cr.competition_id = c.id
                  {$whereSql}
                ";
                $fallbackStmt = $db->prepare($fallbackReportSql);
                if ($hasFrom && $hasTo) {
                    $fallbackStmt->bind_param('ss', $dateFrom, $dateTo);
                } elseif ($hasFrom) {
                    $fallbackStmt->bind_param('s', $dateFrom);
                } elseif ($hasTo) {
                    $fallbackStmt->bind_param('s', $dateTo);
                }
                $fallbackStmt->execute();
                $report = $fallbackStmt->get_result()->fetch_assoc() ?: [];
            } catch (Throwable $fallbackError) {
                $report = ['competitions_count' => 0, 'participants_count' => 0, 'winners_count' => 0];
            }
        }

        $fetchNames = static function (mysqli $db, string $condition): array {
            try {
                $q = "SELECT name FROM competitions WHERE {$condition} ORDER BY competition_date ASC, id ASC LIMIT 30";
                $res = $db->query($q);
                if (!$res) {
                    return [];
                }
                return array_map(static fn (array $row): string => (string) ($row['name'] ?? ''), $res->fetch_all(MYSQLI_ASSOC));
            } catch (Throwable $e) {
                return [];
            }
        };
        $periodNames = [
            'past' => $fetchNames($db, 'competition_date < CURDATE()'),
            'upcoming_15' => $fetchNames($db, 'competition_date >= CURDATE() AND competition_date <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)'),
            'upcoming_after_15' => $fetchNames($db, 'competition_date > DATE_ADD(CURDATE(), INTERVAL 15 DAY)'),
        ];

        return [
            'items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC),
            'pagination' => $meta,
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo, 'period' => $period],
            'report' => [
                'competitions_count' => (int) ($report['competitions_count'] ?? 0),
                'participants_count' => (int) ($report['participants_count'] ?? 0),
                'winners_count' => (int) ($report['winners_count'] ?? 0),
                'period_names' => $periodNames,
            ],
        ];
    }

    if ($page === 'competition_detail') {
        $id = qp_int($input, 'id', 0, 0);
        $stmt = $db->prepare('SELECT id, name, description, registration_deadline, competition_date, location FROM competitions WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $competition = $stmt->get_result()->fetch_assoc() ?: null;

        $participants = [];
        $results = [];
        if ($competition) {
            $pStmt = $db->prepare('SELECT cp.id, cp.student_id, s.fio FROM competition_participants cp JOIN students s ON s.id=cp.student_id WHERE cp.competition_id=? ORDER BY s.fio ASC');
            $pStmt->bind_param('i', $id);
            $pStmt->execute();
            $participants = $pStmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $rStmt = $db->prepare("
              SELECT cr.id, cr.student_id, cr.position, cr.cash_amount, cr.award_type_id, crt.code AS award_code, crt.name AS award_name, s.fio
              FROM competition_results cr
              JOIN students s ON s.id = cr.student_id
              LEFT JOIN competition_result_types crt ON crt.id = cr.award_type_id
              WHERE cr.competition_id=?
              ORDER BY
                CASE WHEN cr.position IS NULL THEN 1 ELSE 0 END ASC,
                cr.position ASC,
                s.fio ASC
            ");
            $rStmt->bind_param('i', $id);
            $rStmt->execute();
            $results = $rStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return ['competition' => $competition, 'participants' => $participants, 'results' => $results];
    }

    if ($page === 'schedule') {
        $type = qp_str($input, 'type');
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 10;
        $where = in_array($type, ['daily', 'weekly'], true) ? ' WHERE type=? ' : '';

        $countSql = 'SELECT COUNT(*) cnt FROM schedule' . $where;
        $countStmt = $db->prepare($countSql);
        if ($where !== '') $countStmt->bind_param('s', $type);
        $countStmt->execute();
        $total = (int) (($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0));
        $meta = paginate_meta($total, $pageNum, $perPage);

        $sql = 'SELECT id, type, title, date FROM schedule' . $where . ' ORDER BY date DESC, id DESC LIMIT ? OFFSET ?';
        $stmt = $db->prepare($sql);
        if ($where !== '') {
            $stmt->bind_param('sii', $type, $meta['per_page'], $meta['offset']);
        } else {
            $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        }
        $stmt->execute();
        return ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => $meta, 'filters' => ['type' => $type]];
    }

    if ($page === 'directions') {
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 20;
        $totalRes = $db->query('SELECT COUNT(*) cnt FROM directions');
        $total = (int) (($totalRes ? $totalRes->fetch_assoc()['cnt'] : 0) ?? 0);
        $meta = paginate_meta($total, $pageNum, $perPage);
        $stmt = $db->prepare('SELECT id, name FROM directions ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        $stmt->execute();
        return ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => $meta];
    }

    if ($page === 'statuses') {
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 20;
        $totalRes = $db->query('SELECT COUNT(*) cnt FROM statuses');
        $total = (int) (($totalRes ? $totalRes->fetch_assoc()['cnt'] : 0) ?? 0);
        $meta = paginate_meta($total, $pageNum, $perPage);
        $stmt = $db->prepare('SELECT id, name FROM statuses ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        $stmt->execute();
        return ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => $meta];
    }

    if ($page === 'teams') {
        $level = qp_str($input, 'level');
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 8;
        $where = in_array($level, ['junior', 'middle', 'senior'], true) ? ' WHERE level = ? ' : '';
        $countSql = 'SELECT COUNT(*) cnt FROM teams' . $where;
        $countStmt = $db->prepare($countSql);
        if ($where !== '') {
            $countStmt->bind_param('s', $level);
        }
        $countStmt->execute();
        $total = (int) (($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0));
        $meta = paginate_meta($total, $pageNum, $perPage);

        $sql = 'SELECT id, team_name, level, created_at FROM teams' . $where . ' ORDER BY id DESC LIMIT ? OFFSET ?';
        $stmt = $db->prepare($sql);
        if ($where !== '') {
            $stmt->bind_param('sii', $level, $meta['per_page'], $meta['offset']);
        } else {
            $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        }
        $stmt->execute();
        $teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $memberStmt = $db->prepare('SELECT tm.id, tm.team_id, tm.student_id, s.fio FROM team_members tm JOIN students s ON s.id=tm.student_id WHERE tm.team_id=? ORDER BY s.fio ASC');
        foreach ($teams as &$team) {
            $teamId = (int) $team['id'];
            $memberStmt->bind_param('i', $teamId);
            $memberStmt->execute();
            $team['members'] = $memberStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        unset($team);

        return ['items' => $teams, 'pagination' => $meta, 'filters' => ['level' => $level]];
    }

    if ($page === 'projects') {
        $status = qp_str($input, 'status');
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 8;
        $where = in_array($status, ['boshlanish', 'qurish', 'testlash', 'tugallash'], true) ? ' WHERE status = ? ' : '';

        $countSql = 'SELECT COUNT(*) cnt FROM projects' . $where;
        $countStmt = $db->prepare($countSql);
        if ($where !== '') {
            $countStmt->bind_param('s', $status);
        }
        $countStmt->execute();
        $total = (int) (($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0));
        $meta = paginate_meta($total, $pageNum, $perPage);

        $sql = 'SELECT id, project_name, status, created_at FROM projects' . $where . ' ORDER BY id DESC LIMIT ? OFFSET ?';
        $stmt = $db->prepare($sql);
        if ($where !== '') {
            $stmt->bind_param('sii', $status, $meta['per_page'], $meta['offset']);
        } else {
            $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        }
        $stmt->execute();
        $projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $memberStmt = $db->prepare('SELECT pm.id, pm.project_id, pm.student_id, s.fio FROM project_members pm JOIN students s ON s.id=pm.student_id WHERE pm.project_id=? ORDER BY s.fio ASC');
        foreach ($projects as &$project) {
            $projectId = (int) $project['id'];
            $memberStmt->bind_param('i', $projectId);
            $memberStmt->execute();
            $project['members'] = $memberStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        unset($project);

        return ['items' => $projects, 'pagination' => $meta, 'filters' => ['status' => $status]];
    }

    return [];
}
