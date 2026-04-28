<?php
require_once __DIR__ . '/../api/bootstrap.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$search = '%' . clean_input($_GET['search'] ?? '') . '%';
$directionId = (int) ($_GET['direction_id'] ?? 0);
$directionFilter = $directionId > 0 ? ' AND s.yonalish_id = ? ' : '';

$countSql = '
  SELECT COUNT(*) AS cnt
  FROM students s
  JOIN directions d ON d.id = s.yonalish_id
  WHERE (s.fio LIKE ? OR d.name LIKE ? OR s.guruh LIKE ?)
' . $directionFilter;
$countStmt = $db->prepare($countSql);
if ($directionId > 0) {
    $countStmt->bind_param('sssi', $search, $search, $search, $directionId);
} else {
    $countStmt->bind_param('sss', $search, $search, $search);
}
$countStmt->execute();
$total = (int) ($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0);

$sql = "
  SELECT
    s.id,
    s.fio,
    s.yonalish_id,
    d.name AS yonalish,
    s.guruh,
    s.kirgan_yili,
    s.telefon,
    s.telegram_chat_id,
    GROUP_CONCAT(DISTINCT st.id ORDER BY st.id SEPARATOR '||') AS status_ids_raw,
    GROUP_CONCAT(DISTINCT st.name ORDER BY st.name SEPARATOR '||') AS status_names
  FROM students s
  JOIN directions d ON d.id = s.yonalish_id
  LEFT JOIN student_status ss ON ss.student_id = s.id
  LEFT JOIN statuses st ON st.id = ss.status_id
  WHERE (s.fio LIKE ? OR d.name LIKE ? OR s.guruh LIKE ?)
  {$directionFilter}
  GROUP BY s.id, s.fio, s.yonalish_id, d.name, s.guruh, s.kirgan_yili, s.telefon, s.telegram_chat_id
  ORDER BY s.id DESC
  LIMIT ? OFFSET ?
";
$stmt = $db->prepare($sql);
if ($directionId > 0) {
    $stmt->bind_param('sssiii', $search, $search, $search, $directionId, $limit, $offset);
} else {
    $stmt->bind_param('sssii', $search, $search, $search, $limit, $offset);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($rows as &$row) {
    $statusNames = trim((string) ($row['status_names'] ?? ''));
    $statusIdsRaw = trim((string) ($row['status_ids_raw'] ?? ''));
    $row['statuses'] = $statusNames === '' ? [] : explode('||', $statusNames);
    $row['status_ids'] = $statusIdsRaw === '' ? [] : array_map('intval', explode('||', $statusIdsRaw));
    unset($row['status_names']);
    unset($row['status_ids_raw']);
}
unset($row);

json_response(true, 'Talabalar olindi.', [
    'items' => $rows,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'pages' => (int) ceil($total / $limit),
    ],
]);
