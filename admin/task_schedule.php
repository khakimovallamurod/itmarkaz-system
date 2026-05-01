<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';

$db = (new Database())->connect();
$db->set_charset('utf8mb4');

try {
    $db->query("
        CREATE TABLE IF NOT EXISTS task_schedule (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(180) NOT NULL,
            deadline DATE NOT NULL,
            file_path VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            target_groups LONGTEXT DEFAULT NULL,
            student_ids LONGTEXT DEFAULT NULL,
            course_student_ids LONGTEXT DEFAULT NULL,
            mentor_ids LONGTEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_task_schedule_deadline (deadline)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Task jadvali jadvalini yaratishda xatolik: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}

function json_response(bool $success, string $message, array $data = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function clean_text(?string $value): string
{
    return trim((string) $value);
}

function clean_int_array(array $values): array
{
    $clean = [];
    foreach ($values as $value) {
        $id = (int) $value;
        if ($id > 0) {
            $clean[$id] = $id;
        }
    }
    return array_values($clean);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function short_text(string $value, int $length = 60): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $length);
    }
    return substr($value, 0, $length);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_task') {
        $title = clean_text($_POST['title'] ?? '');
        $deadline = clean_text($_POST['deadline'] ?? '');
        $description = clean_text($_POST['description'] ?? '');

        if ($title === '' || $deadline === '') {
            json_response(false, 'Topshiriq nomi va muddati majburiy.');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
            json_response(false, 'Sana formati noto‘g‘ri.');
        }

        $allowedGroups = ['talabalar', 'kurs_oqvchilari', 'mentorlar'];
        $targetGroups = array_values(array_intersect($allowedGroups, (array) ($_POST['target_groups'] ?? [])));
        $studentIds = clean_int_array((array) ($_POST['student_ids'] ?? []));
        $courseStudentIds = clean_int_array((array) ($_POST['course_student_ids'] ?? []));
        $mentorIds = clean_int_array((array) ($_POST['mentor_ids'] ?? []));

        $filePath = null;
        if (!empty($_FILES['task_file']['name']) && (int) ($_FILES['task_file']['error'] ?? 1) === 0) {
            $uploadDir = __DIR__ . '/../uploads/task_files';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $originalName = basename((string) $_FILES['task_file']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $safeName = uniqid('task_', true) . ($ext !== '' ? '.' . preg_replace('/[^a-z0-9]/', '', $ext) : '');
            $targetPath = $uploadDir . '/' . $safeName;

            if (!move_uploaded_file((string) $_FILES['task_file']['tmp_name'], $targetPath)) {
                json_response(false, 'Fayl yuklashda xatolik.');
            }

            $filePath = 'uploads/task_files/' . $safeName;
        }

        $stmt = $db->prepare("
            INSERT INTO task_schedule (title, deadline, file_path, description, target_groups, student_ids, course_student_ids, mentor_ids)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $groupsJson = json_encode($targetGroups, JSON_UNESCAPED_UNICODE);
        $studentsJson = json_encode($studentIds, JSON_UNESCAPED_UNICODE);
        $courseStudentsJson = json_encode($courseStudentIds, JSON_UNESCAPED_UNICODE);
        $mentorsJson = json_encode($mentorIds, JSON_UNESCAPED_UNICODE);
        $stmt->bind_param('ssssssss', $title, $deadline, $filePath, $description, $groupsJson, $studentsJson, $courseStudentsJson, $mentorsJson);
        $stmt->execute();

        $taskId = (int) $db->insert_id;
        $shortInfo = short_text($description, 60);
        json_response(true, 'Topshiriq saqlandi.', [
            'event' => [
                'id' => $taskId,
                'title' => $title,
                'start' => $deadline,
                'short' => $shortInfo,
            ],
        ]);
    }

    if ($action === 'move_task') {
        $taskId = (int) ($_POST['id'] ?? 0);
        $newDate = clean_text($_POST['date'] ?? '');

        if ($taskId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
            json_response(false, 'Yaroqsiz ma’lumot.');
        }

        $stmt = $db->prepare("UPDATE task_schedule SET deadline = ? WHERE id = ?");
        $stmt->bind_param('si', $newDate, $taskId);
        $stmt->execute();

        json_response(true, 'Sana yangilandi.');
    }

    json_response(false, 'Noma’lum amal.');
}

$tasks = [];
$resTasks = $db->query("SELECT id, title, deadline, description FROM task_schedule ORDER BY deadline ASC, id DESC");
if ($resTasks) {
    while ($row = $resTasks->fetch_assoc()) {
        $tasks[] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'start' => $row['deadline'],
            'short' => short_text((string) ($row['description'] ?? ''), 60),
        ];
    }
}

$students = [];
$resStudents = $db->query("SELECT id, fio FROM students ORDER BY fio ASC");
if ($resStudents) {
    while ($row = $resStudents->fetch_assoc()) {
        $students[] = ['id' => (int) $row['id'], 'label' => $row['fio']];
    }
}

$courseStudents = [];
$resCourseStudents = $db->query("
    SELECT cs.id, s.fio, c.name AS course_name
    FROM course_students cs
    INNER JOIN students s ON s.id = cs.student_id
    INNER JOIN courses c ON c.id = cs.course_id
    ORDER BY s.fio ASC
");
if ($resCourseStudents) {
    while ($row = $resCourseStudents->fetch_assoc()) {
        $courseStudents[] = [
            'id' => (int) $row['id'],
            'label' => $row['fio'] . ' - ' . $row['course_name'],
        ];
    }
}

$mentors = [];
$resMentors = $db->query("
    SELECT m.id, s.fio, c.name AS course_name
    FROM mentors m
    INNER JOIN students s ON s.id = m.student_id
    INNER JOIN courses c ON c.id = m.course_id
    ORDER BY s.fio ASC
");
if ($resMentors) {
    while ($row = $resMentors->fetch_assoc()) {
        $mentors[] = [
            'id' => (int) $row['id'],
            'label' => $row['fio'] . ' - Mentor (' . $row['course_name'] . ')',
        ];
    }
}
?>
<!doctype html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ish jadvali</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <style>
        .fc .fc-button-primary {
            background-color: #10b981;
            border-color: #10b981;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
        }
        .fc .fc-button-primary:hover,
        .fc .fc-button-primary:focus {
            background-color: #059669;
            border-color: #059669;
        }
        .fc .fc-button-primary.fc-button-active {
            background-color: #047857;
            border-color: #047857;
        }
        .fc .fc-daygrid-event,
        .fc .fc-timegrid-event {
            background-color: #10b981;
            border: none;
            border-radius: 0.5rem;
            padding: 2px 6px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        }
        .fc-event-title {
            font-weight: 600;
            font-size: 12px;
        }
    </style>
</head>
<body class="bg-white text-slate-800 min-h-screen">
<div class="max-w-7xl mx-auto p-4 md:p-6 space-y-4 md:space-y-6">
    <div class="bg-white shadow-md rounded-2xl p-4 md:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl md:text-2xl font-semibold">Ish jadvali</h1>
        <button id="openModalBtn" type="button" class="bg-emerald-500 text-white rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-emerald-600 transition">
            + Yangi topshiriq qo‘shish
        </button>
    </div>

    <div class="bg-white shadow-md rounded-2xl p-3 md:p-4">
        <div id="calendar"></div>
    </div>
</div>

<div id="taskModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-md w-full max-w-2xl p-4 md:p-5 transform transition-all duration-200 scale-95 opacity-0" id="taskModalPanel">
        <h2 class="text-lg font-semibold mb-4">Yangi topshiriq</h2>
        <form id="taskForm" class="grid grid-cols-1 md:grid-cols-2 gap-3" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_task">

            <label class="block md:col-span-1">
                <span class="text-sm font-medium text-slate-700">Topshiriq nomi</span>
                <input type="text" name="title" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </label>

            <label class="block md:col-span-1">
                <span class="text-sm font-medium text-slate-700">Muddati</span>
                <input type="date" name="deadline" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700">Fayl (ixtiyoriy)</span>
                <input type="file" name="task_file" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700">Izoh</span>
                <textarea name="description" rows="3" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400"></textarea>
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700">Kimlar uchunligi</span>
                <select name="target_groups[]" multiple class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm min-h-24 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="talabalar">Talabalar</option>
                    <option value="kurs_oqvchilari">Kurs o‘quvchilari</option>
                    <option value="mentorlar">Mentorlar</option>
                </select>
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700">Talabalar (bir nechta)</span>
                <select name="student_ids[]" multiple class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm min-h-24 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <?php foreach ($students as $student): ?>
                        <option value="<?= (int) $student['id']; ?>"><?= e($student['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700">Kurs o‘quvchilari (bir nechta)</span>
                <select name="course_student_ids[]" multiple class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm min-h-24 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <?php foreach ($courseStudents as $courseStudent): ?>
                        <option value="<?= (int) $courseStudent['id']; ?>"><?= e($courseStudent['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700">Mentorlar (bir nechta)</span>
                <select name="mentor_ids[]" multiple class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm min-h-24 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <?php foreach ($mentors as $mentor): ?>
                        <option value="<?= (int) $mentor['id']; ?>"><?= e($mentor['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                <button type="button" id="cancelModalBtn" class="rounded-lg px-3 py-1.5 text-sm border border-slate-300 hover:bg-slate-100">
                    Cancel
                </button>
                <button type="submit" class="rounded-lg px-3 py-1.5 text-sm bg-emerald-500 text-white hover:bg-emerald-600">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const existingEvents = <?= json_encode($tasks, JSON_UNESCAPED_UNICODE); ?>;
    const modal = document.getElementById('taskModal');
    const modalPanel = document.getElementById('taskModalPanel');
    const form = document.getElementById('taskForm');

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            modalPanel.classList.remove('scale-95', 'opacity-0');
            modalPanel.classList.add('scale-100', 'opacity-100');
        });
    }

    function closeModal() {
        modalPanel.classList.remove('scale-100', 'opacity-100');
        modalPanel.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 160);
    }

    document.getElementById('openModalBtn').addEventListener('click', openModal);
    document.getElementById('cancelModalBtn').addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'uz',
        height: 'auto',
        editable: true,
        dayMaxEvents: true,
        events: existingEvents,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listDay'
        },
        buttonText: {
            today: 'Today',
            month: 'Oy',
            week: 'Xafta',
            list: 'Kun tartibi'
        },
        eventContent: function(arg) {
            const short = arg.event.extendedProps.short || '';
            const shortHtml = short ? '<div style="font-size:11px;line-height:1.2;opacity:0.95;">' + short + '</div>' : '';
            return { html: '<div><div class="fc-event-title">' + arg.event.title + '</div>' + shortHtml + '</div>' };
        },
        eventDrop: function(info) {
            const newDate = info.event.startStr.slice(0, 10);
            const payload = new FormData();
            payload.append('action', 'move_task');
            payload.append('id', info.event.id);
            payload.append('date', newDate);

            fetch('task_schedule.php', {
                method: 'POST',
                body: payload
            })
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        info.revert();
                        alert(result.message || 'Yangilashda xatolik');
                        return;
                    }
                    console.log('Task date updated:', result.message);
                    alert('Topshiriq sanasi yangilandi');
                })
                .catch(() => {
                    info.revert();
                    alert('Server bilan bog‘lanishda xatolik');
                });
        }
    });

    calendar.render();

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const payload = new FormData(form);

        fetch('task_schedule.php', {
            method: 'POST',
            body: payload
        })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    alert(result.message || 'Saqlashda xatolik');
                    return;
                }

                if (result.data && result.data.event) {
                    calendar.addEvent(result.data.event);
                }

                form.reset();
                closeModal();
                alert('Topshiriq saqlandi');
            })
            .catch(() => {
                alert('Server xatosi yuz berdi');
            });
    });
</script>
</body>
</html>
