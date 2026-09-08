<?php
require_once __DIR__ . '/../admin/data_provider.php';

function load_superadmin_page_data(mysqli $db, string $page, array $input): array
{
    if ($page === 'admins') {
        $search = qp_str($input, 'search');
        $status = qp_str($input, 'status');
        $q = '%' . $search . '%';

        $where = "WHERE (a.username LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ? OR a.phone LIKE ?)";
        if ($status === 'active' || $status === 'blocked') {
            $statusEsc = $db->real_escape_string($status);
            $where .= " AND a.status = '$statusEsc'";
        }

        $sql = "
            SELECT 
                a.id, a.username, a.first_name, a.last_name, a.phone, a.role, a.status, a.student_id, a.created_at,
                s.fio as student_fio, s.guruh as student_guruh
            FROM admins a
            LEFT JOIN students s ON s.id = a.student_id
            $where
            ORDER BY (a.role = 'superadmin') DESC, a.id DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ssss', $q, $q, $q, $q);
        $stmt->execute();
        $admins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Resident students for admin creation dropdown
        $resSql = "
            SELECT DISTINCT s.id, s.fio, s.telefon, s.guruh, d.name as direction
            FROM students s
            JOIN student_status ss ON ss.student_id = s.id
            JOIN statuses st ON st.id = ss.status_id AND LOWER(TRIM(st.name)) = 'rezident'
            LEFT JOIN directions d ON d.id = s.yonalish_id
            ORDER BY s.fio ASC
        ";
        $resStmt = $db->query($resSql);
        $residentStudents = $resStmt ? $resStmt->fetch_all(MYSQLI_ASSOC) : [];

        // Count stats
        $totalAdmins = (int) ($db->query("SELECT COUNT(*) FROM admins")->fetch_row()[0] ?? 0);
        $activeAdmins = (int) ($db->query("SELECT COUNT(*) FROM admins WHERE status='active'")->fetch_row()[0] ?? 0);
        $blockedAdmins = (int) ($db->query("SELECT COUNT(*) FROM admins WHERE status='blocked'")->fetch_row()[0] ?? 0);

        return [
            'admins' => $admins,
            'resident_students' => $residentStudents,
            'stats' => [
                'total' => $totalAdmins,
                'active' => $activeAdmins,
                'blocked' => $blockedAdmins
            ],
            'filters' => [
                'search' => $search,
                'status' => $status
            ]
        ];
    }

    if ($page === 'notifications') {
        $tab = qp_str($input, 'tab', 'all');
        $where = "1=1";
        if ($tab === 'students') {
            $where = "l.module = 'students'";
        } elseif ($tab === 'payments') {
            $where = "l.module = 'payments'";
        } elseif ($tab === 'admins') {
            $where = "l.module IN ('admins', 'auth')";
        } elseif ($tab === 'other') {
            $where = "l.module NOT IN ('students', 'payments', 'admins', 'auth')";
        }

        $sql = "
            SELECT 
                l.id, l.admin_id, l.action_type, l.module, l.description, l.target_id, l.is_read, l.created_at,
                a.username, a.first_name, a.last_name, a.role
            FROM admin_activity_logs l
            LEFT JOIN admins a ON a.id = l.admin_id
            WHERE $where
            ORDER BY l.id DESC
            LIMIT 100
        ";
        $res = $db->query($sql);
        $notifications = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        $unreadCount = get_unread_notifications_count($db);

        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'current_tab' => $tab
        ];
    }

    return load_page_data($db, $page, $input);
}
