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
    $needsResidentStudents = in_array($page, ['mentors'], true);
    $needsStudentOptions = in_array($page, ['teams', 'competition_detail'], true);
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
        $sql = "
          SELECT DISTINCT s.id, s.fio
          FROM students s
          JOIN student_status ss ON ss.student_id = s.id
          JOIN statuses st ON st.id = ss.status_id
          WHERE LOWER(TRIM(st.name)) = LOWER('Rezident')
          ORDER BY s.fio ASC
        ";
        $res = $db->query($sql);
        $options['resident_students'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
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
          JOIN student_status ss ON ss.student_id=s.id
          JOIN statuses st ON st.id=ss.status_id AND LOWER(TRIM(st.name))=LOWER('Rezident')
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
          JOIN student_status ss ON ss.student_id=s.id
          JOIN statuses st ON st.id=ss.status_id AND LOWER(TRIM(st.name))=LOWER('Rezident')
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
          JOIN student_status ss ON ss.student_id=s.id
          JOIN statuses st ON st.id=ss.status_id AND LOWER(TRIM(st.name))=LOWER('Kurs o''quvchi')
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
          SELECT DISTINCT s.id AS student_id, s.fio, cs.id, cs.course_id, c.name AS course_name, cs.room_id, r.room_number, COALESCE(cs.status,'active') AS status
          FROM students s
          JOIN student_status ss ON ss.student_id=s.id
          JOIN statuses st ON st.id=ss.status_id AND LOWER(TRIM(st.name))=LOWER('Kurs o''quvchi')
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
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 9;
        $totalRes = $db->query('SELECT COUNT(*) cnt FROM competitions');
        $total = (int) (($totalRes ? $totalRes->fetch_assoc()['cnt'] : 0) ?? 0);
        $meta = paginate_meta($total, $pageNum, $perPage);
        $stmt = $db->prepare("SELECT c.id, c.name, c.description, c.registration_deadline, c.competition_date, c.location, (SELECT COUNT(*) FROM competition_participants cp WHERE cp.competition_id=c.id) AS participant_count, (SELECT COUNT(*) FROM competition_results cr WHERE cr.competition_id=c.id) AS result_count FROM competitions c ORDER BY c.competition_date DESC, c.id DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
        $stmt->execute();
        return ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => $meta];
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

            $rStmt = $db->prepare('SELECT cr.id, cr.student_id, cr.position, s.fio FROM competition_results cr JOIN students s ON s.id=cr.student_id WHERE cr.competition_id=? ORDER BY cr.position ASC');
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
        $pageNum = qp_int($input, 'p', 1, 1);
        $perPage = 8;
        $totalRes = $db->query('SELECT COUNT(*) cnt FROM teams');
        $total = (int) (($totalRes ? $totalRes->fetch_assoc()['cnt'] : 0) ?? 0);
        $meta = paginate_meta($total, $pageNum, $perPage);

        $stmt = $db->prepare('SELECT id, team_name, created_at FROM teams ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $meta['per_page'], $meta['offset']);
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

        return ['items' => $teams, 'pagination' => $meta];
    }

    return [];
}
