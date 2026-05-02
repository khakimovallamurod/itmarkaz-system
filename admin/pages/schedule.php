<?php
if (!isset($db) || !($db instanceof mysqli)) {
    echo '<div class="p-4 text-red-600">DB ulanish topilmadi.</div>';
    return;
}

if (!function_exists('schedule_e')) {
    function schedule_e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('schedule_json_response')) {
    function schedule_json_response(bool $success, string $message, array $data = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_GET['page'] ?? '') === 'schedule')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_task') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $deadline = trim((string) ($_POST['deadline'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($title === '' || $deadline === '') {
            schedule_json_response(false, 'Topshiriq nomi va muddati majburiy.');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
            schedule_json_response(false, 'Sana formati xato.');
        }

        $allowedGroups = ['talaba', 'kurs_oqvchisi', 'mentor'];
        $groupAliases = [
            'rezident' => 'talaba',
            'rezidentlar' => 'talaba',
            'talabalar' => 'talaba',
            'talaba' => 'talaba',
            'kurs_oqvchilari' => 'kurs_oqvchisi',
            'kurs_oqvchisi' => 'kurs_oqvchisi',
            'mentorlar' => 'mentor',
            'mentor' => 'mentor',
        ];
        $rawGroups = (array) ($_POST['target_groups'] ?? []);
        $normalizedGroups = [];
        foreach ($rawGroups as $group) {
            $key = trim((string) $group);
            if (isset($groupAliases[$key])) {
                $normalizedGroups[] = $groupAliases[$key];
            }
        }
        $targetGroups = array_values(array_unique(array_intersect($allowedGroups, $normalizedGroups)));
        $studentIds = array_values(array_unique(array_map('intval', (array) ($_POST['student_ids'] ?? []))));
        $courseStudentIds = array_values(array_unique(array_map('intval', (array) ($_POST['course_student_ids'] ?? []))));
        $mentorIds = array_values(array_unique(array_map('intval', (array) ($_POST['mentor_ids'] ?? []))));

        $filePath = null;
        if (!empty($_FILES['task_file']['name']) && (int) ($_FILES['task_file']['error'] ?? 1) === 0) {
            $uploadDir = __DIR__ . '/../../uploads/task_files';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $originalName = basename((string) $_FILES['task_file']['name']);
            $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
            $safeExt = preg_replace('/[^a-z0-9]/', '', $ext);
            $fileName = uniqid('task_', true) . ($safeExt !== '' ? '.' . $safeExt : '');
            $targetPath = $uploadDir . '/' . $fileName;

            if (!move_uploaded_file((string) $_FILES['task_file']['tmp_name'], $targetPath)) {
                schedule_json_response(false, 'Fayl yuklashda xatolik.');
            }
            $filePath = 'uploads/task_files/' . $fileName;
        }

        $stmt = $db->prepare("
            INSERT INTO task_schedule (title, deadline, file_path, description, target_groups, student_ids, course_student_ids, mentor_ids)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $tg = json_encode($targetGroups, JSON_UNESCAPED_UNICODE);
        $st = json_encode($studentIds, JSON_UNESCAPED_UNICODE);
        $cs = json_encode($courseStudentIds, JSON_UNESCAPED_UNICODE);
        $mt = json_encode($mentorIds, JSON_UNESCAPED_UNICODE);
        $stmt->bind_param('ssssssss', $title, $deadline, $filePath, $description, $tg, $st, $cs, $mt);
        $stmt->execute();

        schedule_json_response(true, 'Topshiriq saqlandi.', [
            'event' => [
                'id' => (int) $db->insert_id,
                'title' => $title,
                'start' => $deadline,
                'short' => function_exists('mb_substr') ? mb_substr($description, 0, 60) : substr($description, 0, 60),
            ],
        ]);
    }

    if ($action === 'move_task') {
        $id = (int) ($_POST['id'] ?? 0);
        $date = trim((string) ($_POST['date'] ?? ''));
        if ($id <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            schedule_json_response(false, 'Noto‘g‘ri ma’lumot.');
        }

        $stmt = $db->prepare('UPDATE task_schedule SET deadline = ? WHERE id = ?');
        $stmt->bind_param('si', $date, $id);
        $stmt->execute();
        schedule_json_response(true, 'Sana yangilandi.');
    }

    schedule_json_response(false, 'Noma’lum action.');
}

$tasks = [];
$qTasks = $db->query('SELECT id, title, deadline, description FROM task_schedule ORDER BY deadline ASC, id DESC');
if ($qTasks) {
    while ($row = $qTasks->fetch_assoc()) {
        $desc = (string) ($row['description'] ?? '');
        $tasks[] = [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'start' => (string) $row['deadline'],
            'short' => function_exists('mb_substr') ? mb_substr($desc, 0, 60) : substr($desc, 0, 60),
        ];
    }
}

?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
    #tsm-calendar .fc .fc-button-primary {
        background-color: #10b981;
        border-color: #10b981;
        border-radius: 0.5rem;
        font-size: 0.8rem;
        padding: 0.35rem 0.65rem;
    }
    #tsm-calendar .fc .fc-button-primary:hover,
    #tsm-calendar .fc .fc-button-primary:focus {
        background-color: #059669;
        border-color: #059669;
    }
    #tsm-calendar .fc .fc-button-primary.fc-button-active {
        background-color: #047857;
        border-color: #047857;
    }
    #tsm-calendar .fc .fc-daygrid-event,
    #tsm-calendar .fc .fc-timegrid-event {
        background-color: #10b981;
        border: none;
        border-radius: 0.5rem;
        padding: 2px 6px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.22);
    }
    #tsm-calendar .fc-event-title {
        font-weight: 600;
        font-size: 12px;
    }
</style>

<div class="w-full p-0 space-y-4">
    <div class="bg-white rounded-xl shadow-md p-4 md:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-xl font-semibold text-slate-900">Ish jadvali</h2>
        <button id="tsm-open-btn" type="button" class="rounded-lg px-3 py-1.5 text-sm bg-emerald-500 text-white hover:bg-emerald-600 transition">
            + Yangi topshiriq qo‘shish
        </button>
    </div>

    <div id="tsm-calendar" class="bg-white rounded-xl shadow-md p-2 md:p-3 w-full">
        <div id="tsm-calendar-root"></div>
    </div>
</div>

<div id="tsm-modal" class="fixed inset-0 hidden items-center justify-center p-4 z-50 bg-black/40">
    <div id="tsm-modal-panel" class="w-full max-w-2xl bg-white rounded-xl shadow-md p-4 md:p-5 transform scale-95 opacity-0 transition-all duration-200">
        <h3 class="text-lg font-semibold mb-4">Yangi topshiriq</h3>
        <form id="tsm-form" class="grid grid-cols-1 md:grid-cols-2 gap-3" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_task">

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Topshiriq nomi</span>
                <input type="text" name="title" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Muddati</span>
                <input type="date" name="deadline" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700">Fayl (faqat PDF)</span>
                <input type="file" name="task_file" accept=".pdf" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700">Izoh</span>
                <textarea name="description" rows="3" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400"></textarea>
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700">Kimlar uchunligi</span>
                <div class="mt-2 grid gap-2 rounded-lg border border-slate-300 p-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="target_groups[]" value="talaba" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                        <span>Talaba</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="target_groups[]" value="kurs_oqvchisi" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                        <span>Kurs o‘quvchisi</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="target_groups[]" value="mentor" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                        <span>Mentor</span>
                    </label>
                </div>
            </label>

            <div class="md:col-span-2 flex justify-end gap-2">
                <button id="tsm-cancel-btn" type="button" class="rounded-lg px-3 py-1.5 text-sm border border-slate-300 hover:bg-orange-50 hover:border-orange-300">
                    Cancel
                </button>
                <button type="submit" class="rounded-lg px-3 py-1.5 text-sm bg-emerald-500 text-white hover:bg-emerald-600">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Modal -->
<div id="tsm-detail-modal" class="fixed inset-0 hidden items-center justify-center p-4 z-50 bg-black/40">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 id="tsm-detail-title" class="font-bold text-slate-800">Topshiriq nomi</h3>
            <button id="tsm-detail-close" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <i class="fa-solid fa-calendar-day"></i>
                <span id="tsm-detail-date">2026-05-01</span>
            </div>
            <div id="tsm-detail-desc" class="text-slate-700 text-sm leading-relaxed whitespace-pre-wrap">Izoh...</div>
            <button id="tsm-detail-view-pdf" class="hidden inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-medium hover:bg-emerald-100 transition-colors">
                <i class="fa-solid fa-eye"></i>
                PDF ko'rish
            </button>
            <a id="tsm-detail-file-download" href="#" target="_blank" class="hidden inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
                <i class="fa-solid fa-download"></i>
                Yuklab olish
            </a>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
            <button id="tsm-detail-delete" class="h-9 w-9 rounded-xl border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-all duration-200 shadow-sm" title="O'chirish">
                <i class="fa-solid fa-trash-can text-sm"></i>
            </button>
            <div class="flex gap-2">
                <button id="tsm-detail-edit" class="h-9 w-9 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-all duration-200 shadow-sm" title="Tahrirlash">
                    <i class="fa-solid fa-pen-to-square text-sm"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PDF Viewer Modal -->
<div id="tsm-pdf-viewer-modal" class="fixed inset-0 hidden flex-col z-[60] bg-slate-900/90 backdrop-blur-sm">
    <div class="flex items-center justify-between p-4 bg-slate-800 text-white">
        <h3 id="tsm-pdf-title" class="font-medium truncate pr-4">PDF View</h3>
        <div class="flex items-center gap-3">
            <button id="tsm-pdf-close" class="h-10 w-10 flex items-center justify-center rounded-full hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
    </div>
    <div class="flex-1 overflow-hidden relative bg-slate-200">
        <iframe id="tsm-pdf-iframe" src="" class="w-full h-full border-none shadow-inner" style="background-color: #525659;"></iframe>
    </div>
</div>

<script>
(() => {
    const events = <?= json_encode($tasks, JSON_UNESCAPED_UNICODE); ?>;
    const modal = document.getElementById('tsm-modal');
    const panel = document.getElementById('tsm-modal-panel');
    const form = document.getElementById('tsm-form');
    const openBtn = document.getElementById('tsm-open-btn');
    const cancelBtn = document.getElementById('tsm-cancel-btn');
    const toast = (icon, title) => {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon,
                title,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true
            });
            return;
        }
        alert(title);
    };

    const openModal = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            panel.classList.remove('scale-95', 'opacity-0');
        });
    };
    const closeModal = () => {
        panel.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 170);
    };

    openBtn.addEventListener('click', () => {
        form.reset();
        form.querySelector('[name="id"]')?.remove();
        form.querySelector('[name="action"]').value = 'create_task';
        document.querySelector('#tsm-modal h3').textContent = 'Yangi topshiriq';
        openModal();
    });
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    const openDetailModal = async (id) => {
        const res = await fetch(`../get/task_schedule_detail.php?id=${id}`).then(r => r.json());
        if (!res.success) return toast('error', res.message);

        const task = res.data;
        const dModal = document.getElementById('tsm-detail-modal');
        document.getElementById('tsm-detail-title').textContent = task.title;
        document.getElementById('tsm-detail-date').textContent = task.deadline;
        document.getElementById('tsm-detail-desc').textContent = task.description || 'Izoh yo\'q';
        
        const viewPdfBtn = document.getElementById('tsm-detail-view-pdf');
        const downloadBtn = document.getElementById('tsm-detail-file-download');
        
        if (task.file_path) {
            const url = '../' + task.file_path;
            viewPdfBtn.classList.remove('hidden');
            downloadBtn.classList.remove('hidden');
            downloadBtn.href = url;
            
            viewPdfBtn.onclick = () => {
                const pdfModal = document.getElementById('tsm-pdf-viewer-modal');
                const iframe = document.getElementById('tsm-pdf-iframe');
                document.getElementById('tsm-pdf-title').textContent = task.title;
                iframe.src = url;
                pdfModal.classList.remove('hidden');
                pdfModal.classList.add('flex');
            };
        } else {
            viewPdfBtn.classList.add('hidden');
            downloadBtn.classList.add('hidden');
        }
        
        document.getElementById('tsm-pdf-close').onclick = () => {
            const pdfModal = document.getElementById('tsm-pdf-viewer-modal');
            const iframe = document.getElementById('tsm-pdf-iframe');
            iframe.src = '';
            pdfModal.classList.add('hidden');
            pdfModal.classList.remove('flex');
        };

        document.getElementById('tsm-detail-edit').onclick = () => {
            closeDetailModal();
            fillEditForm(task);
        };
        document.getElementById('tsm-detail-delete').onclick = async () => {
            const confirmed = await Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Ushbu topshiriq butunlay o'chiriladi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ha, o\'chirish!',
                cancelButtonText: 'Bekor qilish'
            });

            if (confirmed.isConfirmed) {
                const fd = new FormData();
                fd.append('id', task.id);
                const delRes = await fetch('../delete/task_schedule.php', { method: 'POST', body: fd }).then(r => r.json());
                if (delRes.success) {
                    calendar.getEventById(task.id).remove();
                    closeDetailModal();
                    toast('success', delRes.message);
                } else {
                    toast('error', delRes.message);
                }
            }
        };

        dModal.classList.remove('hidden');
        dModal.classList.add('flex');
    };

    const closeDetailModal = () => {
        const dModal = document.getElementById('tsm-detail-modal');
        dModal.classList.add('hidden');
        dModal.classList.remove('flex');
    };

    const fillEditForm = (task) => {
        form.reset();
        let idInput = form.querySelector('[name="id"]');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            form.appendChild(idInput);
        }
        idInput.value = task.id;
        form.querySelector('[name="action"]').value = 'update_task';
        form.title.value = task.title;
        form.deadline.value = task.deadline;
        form.description.value = task.description || '';
        
        const groups = task.target_groups || [];
        form.querySelectorAll('input[name="target_groups[]"]').forEach(cb => {
            cb.checked = groups.includes(cb.value);
        });

        document.querySelector('#tsm-modal h3').textContent = 'Topshiriqni tahrirlash';
        openModal();
    };

    document.getElementById('tsm-detail-close').onclick = closeDetailModal;

    const calendar = new FullCalendar.Calendar(document.getElementById('tsm-calendar-root'), {
        initialView: 'dayGridMonth',
        editable: true,
        height: 'auto',
        dayMaxEvents: true,
        events,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listDay'
        },
        buttonText: {
            today: 'Bugun',
            month: 'Oy',
            week: 'Xafta',
            list: 'Kun tartibi'
        },
        eventContent: function(arg) {
            const short = arg.event.extendedProps.short || '';
            const shortHtml = short ? '<div style="font-size:11px;opacity:.95;line-height:1.2;">' + short + '</div>' : '';
            return { html: '<div><div class="fc-event-title" style="white-space:normal;">' + arg.event.title + '</div>' + shortHtml + '</div>' };
        },
        eventClick: function(info) {
            openDetailModal(info.event.id);
        },
        eventDrop: function(info) {
            const fd = new FormData();
            fd.append('action', 'move_task');
            fd.append('id', info.event.id);
            fd.append('date', info.event.startStr.slice(0, 10));

            fetch('../update/task_schedule_date.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        info.revert();
                        toast('error', res.message || 'Yangilashda xatolik');
                        return;
                    }
                    toast('success', res.message || 'Sana yangilandi');
                })
                .catch(() => {
                    info.revert();
                    toast('error', 'Server xatosi');
                });
        }
    });
    calendar.render();

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const action = fd.get('action');
        const url = action === 'create_task' ? '../insert/task_schedule.php' : '../update/task_schedule.php';
        
        fetch(url, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    toast('error', res.message || 'Saqlashda xatolik');
                    return;
                }
                if (res.data && res.data.event) {
                    const existing = calendar.getEventById(res.data.event.id);
                    if (existing) existing.remove();
                    calendar.addEvent(res.data.event);
                }
                form.reset();
                closeModal();
                toast('success', res.message || 'Topshiriq saqlandi');
            })
            .catch(() => toast('error', 'Server xatosi'));
    });
})();
</script>
