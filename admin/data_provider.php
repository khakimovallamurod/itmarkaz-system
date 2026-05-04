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

function get_sort_sql(array $input, array $allowedFields, string $defaultSort, string $defaultOrder = 'DESC'): string
{
    $sortBy = qp_str($input, 'sort_by');
    $sortOrder = strtoupper(qp_str($input, 'sort_order'));
    if (!in_array($sortBy, $allowedFields, true)) $sortBy = $defaultSort;
    if (!in_array($sortOrder, ['ASC', 'DESC'], true)) $sortOrder = $defaultOrder;
    return " ORDER BY $sortBy $sortOrder";
}

function fetch_stats_data(mysqli $db): array
{
    $cached = cache_get('dashboard_stats');
    if ($cached) return $cached;

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
        'courses_count' => $count('SELECT COUNT(*) cnt FROM courses'),
        'graduates_count' => $count("SELECT COUNT(*) cnt FROM course_students WHERE status='completed'"),
        'upwork_students_count' => $count('SELECT COUNT(DISTINCT student_id) cnt FROM team_members'),
        'project_students_count' => $count('SELECT COUNT(DISTINCT student_id) cnt FROM project_members'),
        'commercial_contracts_count' => $count('SELECT COUNT(DISTINCT project_id) cnt FROM payments'),
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
      UNION ALL
      (SELECT 'payment' activity_type, CONCAT('To\'lov kiritildi: ', s.fio, ' - ', p.amount) AS title, p.created_at
       FROM payments p
       JOIN students s ON s.id = p.student_id)
      UNION ALL
      (SELECT 'project' activity_type, CONCAT('Loyiha yaratildi: ', project_name) AS title, created_at FROM projects)
      ORDER BY created_at DESC
      LIMIT 10
    ";
    $recentRes = $safe($recentSql);
    if ($recentRes) $stats['recent_activity'] = $recentRes->fetch_all(MYSQLI_ASSOC);

    // Payments summary
    $stats['total_payments'] = $count('SELECT SUM(amount) cnt FROM payments');
    $payTypeRes = $safe("SELECT pt.name, SUM(p.amount) as total FROM payments p JOIN payment_types pt ON pt.id = p.payment_type_id GROUP BY pt.name");
    $stats['payments_by_type'] = $payTypeRes ? $payTypeRes->fetch_all(MYSQLI_ASSOC) : [];

    // Course distribution (Operational)
    $courseDistRes = $safe("SELECT c.name, COUNT(cs.id) as cnt FROM courses c LEFT JOIN course_students cs ON cs.course_id = c.id GROUP BY c.id, c.name ORDER BY cnt DESC LIMIT 6");
    $stats['course_distribution'] = $courseDistRes ? $courseDistRes->fetch_all(MYSQLI_ASSOC) : [];

    // Project status (Operational)
    $projStatusRes = $safe("SELECT status, COUNT(*) as cnt FROM projects GROUP BY status");
    $stats['projects_by_status'] = $projStatusRes ? $projStatusRes->fetch_all(MYSQLI_ASSOC) : [];

    // Room occupancy (Resource)
    $roomOccRes = $safe("SELECT room_number, computers_count, (SELECT COUNT(*) FROM residents r WHERE r.room_id = rooms.id) as residents_count FROM rooms");
    $stats['rooms_occupancy'] = $roomOccRes ? $roomOccRes->fetch_all(MYSQLI_ASSOC) : [];

    // Student directions (Deep Dive)
    $dirDistRes = $safe("SELECT d.name, COUNT(s.id) as cnt FROM directions d LEFT JOIN students s ON s.yonalish_id = d.id GROUP BY d.id, d.name ORDER BY cnt DESC");
    $stats['direction_distribution'] = $dirDistRes ? $dirDistRes->fetch_all(MYSQLI_ASSOC) : [];

    $statsData = [
        'dashboard' => [
            'students' => $stats['students'],
            'residents' => $stats['residents'],
            'mentors' => $stats['mentors'],
            'course_students' => $stats['course_students'],
            'courses_count' => $stats['courses_count'],
            'graduates_count' => $stats['graduates_count'],
            'competitions' => $stats['competitions'],
            'upwork_students_count' => $stats['upwork_students_count'],
            'project_students_count' => $stats['project_students_count'],
            'commercial_contracts_count' => $stats['commercial_contracts_count'],
            'total_payments' => $stats['total_payments'],
            'recent_activity' => $stats['recent_activity'],
            'upcoming_competitions' => $stats['upcoming_competitions'],
            'course_distribution' => $stats['course_distribution'],
            'projects_by_status' => $stats['projects_by_status']
        ],
        'statistics' => [
            'total_payments' => $stats['total_payments'],
            'payments_by_type' => $stats['payments_by_type'],
            'result_distribution' => $stats['result_distribution'],
            'winners' => $stats['winners'],
            'top_students' => $stats['top_students'],
            'rooms_occupancy' => $stats['rooms_occupancy'],
            'direction_distribution' => $stats['direction_distribution'],
            'competitions_count' => $stats['competitions']
        ]
    ];

    cache_set('dashboard_stats', $statsData, 300); 
    return $statsData;
}

function load_page_options(mysqli $db, string $page): array
{
    $options = [];
    $forceClear = isset($_GET['clear_cache']);

    $needsDirections = in_array($page, ['students'], true);
    $needsStatuses = in_array($page, ['students'], true);
    $needsRooms = in_array($page, ['residents', 'course_students'], true);
    $needsCourses = in_array($page, ['course_students', 'mentors'], true);
    $needsResidentStudents = in_array($page, ['mentors', 'residents'], true);
    $needsCourseStudentOptions = in_array($page, ['course_students'], true);
    $needsCompetitionResultTypes = in_array($page, ['competition_detail'], true);
    $needsStudentOptions = in_array($page, ['teams', 'projects', 'competition_detail'], true);
    $needsWeekDays = in_array($page, ['courses'], true);
    $needsPaymentTypes = in_array($page, ['payments'], true);
    $needsAllProjects = in_array($page, ['payments'], true);

    if ($needsDirections) {
        $options['directions'] = $forceClear ? null : cache_get('opt_directions');
        if ($options['directions'] === null) {
            $res = $db->query('SELECT id, name FROM directions ORDER BY name ASC');
            $options['directions'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            cache_set('opt_directions', $options['directions'], 3600);
        }
    }
    if ($needsStatuses) {
        $options['statuses'] = cache_get('opt_statuses');
        if ($options['statuses'] === null) {
            $res = $db->query('SELECT id, name FROM statuses ORDER BY name ASC');
            $options['statuses'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            cache_set('opt_statuses', $options['statuses'], 3600);
        }
    }
    if ($needsRooms) {
        $options['rooms'] = cache_get('opt_rooms');
        if ($options['rooms'] === null) {
            $res = $db->query('SELECT id, room_number FROM rooms ORDER BY room_number ASC');
            $options['rooms'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            cache_set('opt_rooms', $options['rooms'], 3600);
        }
    }
    if ($needsCourses) {
        $options['courses'] = cache_get('opt_courses');
        if ($options['courses'] === null) {
            $res = $db->query('SELECT id, name FROM courses ORDER BY name ASC');
            $options['courses'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            cache_set('opt_courses', $options['courses'], 3600);
        }
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
    if ($needsPaymentTypes) {
        $res = $db->query('SELECT id, name FROM payment_types ORDER BY id ASC');
        $options['payment_types'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    if ($needsAllProjects) {
        $res = $db->query('SELECT id, project_name FROM projects ORDER BY project_name ASC');
        $options['all_projects'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    return $options;
}

function load_page_data(mysqli $db, string $page, array $input): array
{
    if ($page === 'dashboard' || $page === 'statistics') {
        $allStats = fetch_stats_data($db);
        return ['stats' => $allStats[$page] ?? []];
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

        // Additional counts for the bottom of the table
        $totalStudents = (int) ($db->query("SELECT COUNT(*) FROM students")->fetch_row()[0] ?? 0);
        $totalResidents = (int) ($db->query("SELECT COUNT(DISTINCT student_id) FROM student_status WHERE status_id = (SELECT id FROM statuses WHERE name = 'Rezident' LIMIT 1)")->fetch_row()[0] ?? 0);
        $totalCourseStudents = (int) ($db->query("SELECT COUNT(DISTINCT student_id) FROM student_status WHERE status_id = (SELECT id FROM statuses WHERE name = 'Kurs o\'quvchi' LIMIT 1)")->fetch_row()[0] ?? 0);

        $sortSql = get_sort_sql($input, ['s.id', 's.fio', 'd.name', 's.guruh', 's.kirgan_yili'], 's.id', 'DESC');

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
          $sortSql
          LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        if ($directionId > 0) {
            $stmt->bind_param('sssiii', $q, $q, $q, $directionId, $meta['per_page'], $meta['offset']);
        } else {
            $stmt->bind_param('ssiii', $q, $q, $q, $meta['per_page'], $meta['offset']);
        }
        $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($students as &$s) {
            $s['status_ids'] = !empty($s['status_ids_raw']) ? explode('||', $s['status_ids_raw']) : [];
            $s['statuses'] = !empty($s['status_names']) ? explode('||', $s['status_names']) : [];
        }
        unset($s);

        return [
            'items' => $students,
            'pagination' => $meta,
            'stats' => [
                'total' => $totalStudents,
                'residents' => $totalResidents,
                'course_students' => $totalCourseStudents
            ],
            'filters' => [
                'search' => $search,
                'direction_id' => $directionId,
                'sort_by' => qp_str($input, 'sort_by', 's.id'),
                'sort_order' => strtoupper(qp_str($input, 'sort_order', 'DESC'))
            ]
        ];
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

        $sortSql = get_sort_sql($input, ['r.id', 's.fio', 'rm.room_number', 'r.computer_number'], 's.fio', 'ASC');

        $sql = "
          SELECT DISTINCT r.id, s.id AS student_id, s.fio, rm.id AS room_id, rm.room_number, r.computer_number
          FROM students s
          LEFT JOIN residents r ON r.student_id=s.id
          LEFT JOIN rooms rm ON rm.id=r.room_id
          WHERE s.fio LIKE ? {$statusSql}
          $sortSql
          LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sii', $q, $meta['per_page'], $meta['offset']);
        $stmt->execute();
        return [
            'items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 
            'pagination' => $meta, 
            'filters' => [
                'search' => $search, 
                'status' => $status,
                'sort_by' => qp_str($input, 'sort_by', 's.fio'),
                'sort_order' => strtoupper(qp_str($input, 'sort_order', 'ASC'))
            ]
        ];
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

        $sortSql = get_sort_sql($input, ['s.fio', 'c.name', 'r.room_number', 'cs.status'], 's.fio', 'ASC');

        $sql = "
          SELECT DISTINCT s.id AS student_id, s.fio, cs.id, cs.course_id, c.name AS course_name, cs.room_id, r.room_number, cs.status
          FROM students s
          LEFT JOIN course_students cs ON cs.student_id=s.id
          LEFT JOIN courses c ON c.id=cs.course_id
          LEFT JOIN rooms r ON r.id=cs.room_id
          WHERE s.fio LIKE ? {$statusSql}
          $sortSql
          LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        if ($statusSql !== '') {
            $stmt->bind_param('ssii', $q, $status, $meta['per_page'], $meta['offset']);
        } else {
            $stmt->bind_param('sii', $q, $meta['per_page'], $meta['offset']);
        }
        $stmt->execute();
        return [
            'items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 
            'pagination' => $meta, 
            'filters' => [
                'search' => $search, 
                'status' => $status,
                'sort_by' => qp_str($input, 'sort_by', 's.fio'),
                'sort_order' => strtoupper(qp_str($input, 'sort_order', 'ASC'))
            ]
        ];
    }

    if ($page === 'rooms') {
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 10;
        $totalRes = $db->query('SELECT COUNT(*) cnt FROM rooms');
        $total = (int) (($totalRes ? $totalRes->fetch_assoc()['cnt'] : 0) ?? 0);
        $meta = paginate_meta($total, $pageNum, $perPage);
        $sortSql = get_sort_sql($input, ['id', 'room_number', 'capacity', 'computers_count'], 'id', 'DESC');
        $stmt = $db->prepare("SELECT id, room_number, capacity, computers_count FROM rooms $sortSql LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        $stmt->execute();
        return [
            'items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 
            'pagination' => $meta,
            'filters' => [
                'sort_by' => qp_str($input, 'sort_by', 'id'),
                'sort_order' => strtoupper(qp_str($input, 'sort_order', 'DESC'))
            ]
        ];
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

        $teamIds = array_column($teams, 'id');
        if (!empty($teamIds)) {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $mStmt = $db->prepare("SELECT tm.id, tm.team_id, tm.student_id, s.fio FROM team_members tm JOIN students s ON s.id=tm.student_id WHERE tm.team_id IN ($placeholders) ORDER BY s.fio ASC");
            $mStmt->bind_param(str_repeat('i', count($teamIds)), ...$teamIds);
            $mStmt->execute();
            $allMembers = $mStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $grouped = [];
            foreach ($allMembers as $m) $grouped[$m['team_id']][] = $m;
            foreach ($teams as &$t) $t['members'] = $grouped[$t['id']] ?? [];
        }
        unset($t);

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

        $projectIds = array_column($projects, 'id');
        if (!empty($projectIds)) {
            $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
            $mStmt = $db->prepare("SELECT pm.id, pm.project_id, pm.student_id, s.fio FROM project_members pm JOIN students s ON s.id=pm.student_id WHERE pm.project_id IN ($placeholders) ORDER BY s.fio ASC");
            $mStmt->bind_param(str_repeat('i', count($projectIds)), ...$projectIds);
            $mStmt->execute();
            $allMembers = $mStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $grouped = [];
            foreach ($allMembers as $m) $grouped[$m['project_id']][] = $m;
            foreach ($projects as &$p) $p['members'] = $grouped[$p['id']] ?? [];
        }
        unset($p);

        return ['items' => $projects, 'pagination' => $meta, 'filters' => ['status' => $status]];
    }

    if ($page === 'payments') {
        $search = qp_str($input, 'search');
        $projectId = qp_int($input, 'project_id', 0, 0);
        $typeId = qp_int($input, 'payment_type_id', 0, 0);
        $dateFrom = qp_str($input, 'date_from');
        $dateTo = qp_str($input, 'date_to');
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 15;

        $whereParts = ['1=1'];
        $params = [];
        $types = '';

        if ($search !== '') {
            $whereParts[] = '(s.fio LIKE ? OR pr.project_name LIKE ?)';
            $q = "%$search%";
            $params[] = $q; $params[] = $q;
            $types .= 'ss';
        }
        if ($projectId > 0) {
            $whereParts[] = 'p.project_id = ?';
            $params[] = $projectId;
            $types .= 'i';
        }
        if ($typeId > 0) {
            $whereParts[] = 'p.payment_type_id = ?';
            $params[] = $typeId;
            $types .= 'i';
        }
        if ($dateFrom !== '') {
            $whereParts[] = 'DATE(p.created_at) >= ?';
            $params[] = $dateFrom;
            $types .= 's';
        }
        if ($dateTo !== '') {
            $whereParts[] = 'DATE(p.created_at) <= ?';
            $params[] = $dateTo;
            $types .= 's';
        }

        $whereSql = implode(' AND ', $whereParts);

        // Sort
        $sortSql = get_sort_sql($input, ['p.id', 'student_fio', 'pr.project_name', 'p.amount', 'p.created_at'], 'p.id', 'DESC');

        // Total sum for current filters (without pagination)
        $totalSumSql = "SELECT SUM(p.amount) as total FROM payments p JOIN students s ON s.id = p.student_id JOIN projects pr ON pr.id = p.project_id WHERE $whereSql";
        $totalSumStmt = $db->prepare($totalSumSql);
        if ($params) $totalSumStmt->bind_param($types, ...$params);
        $totalSumStmt->execute();
        $totalSum = (float) ($totalSumStmt->get_result()->fetch_assoc()['total'] ?? 0);

        // Count for pagination
        $countSql = "SELECT COUNT(*) as cnt FROM payments p JOIN students s ON s.id = p.student_id JOIN projects pr ON pr.id = p.project_id WHERE $whereSql";
        $countStmt = $db->prepare($countSql);
        if ($params) $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $totalCount = (int) ($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        $meta = paginate_meta($totalCount, $pageNum, $perPage);

        // Main data
        $sql = "
          SELECT p.*, s.fio as student_fio, pr.project_name, pt.name as type_name
          FROM payments p
          JOIN students s ON s.id = p.student_id
          JOIN projects pr ON pr.id = p.project_id
          JOIN payment_types pt ON pt.id = p.payment_type_id
          WHERE $whereSql
          $sortSql
          LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        $params[] = $meta['per_page'];
        $params[] = $meta['offset'];
        $types .= 'ii';
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'items' => $items,
            'total_sum' => $totalSum,
            'pagination' => $meta,
            'filters' => [
                'search' => $search,
                'project_id' => $projectId,
                'payment_type_id' => $typeId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort_by' => qp_str($input, 'sort_by', 'p.id'),
                'sort_order' => strtoupper(qp_str($input, 'sort_order', 'DESC'))
            ]
        ];
    }

    return [];
}
