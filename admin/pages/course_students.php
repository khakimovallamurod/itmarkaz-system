<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? ['search' => '', 'status' => ''];
$courses = $pageOptions['courses'] ?? [];
$rooms = $pageOptions['rooms'] ?? [];
?>
<div class="p-4 space-y-4">
    <form method="get" class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <input type="hidden" name="page" value="course_students">
        <h2 class="font-semibold">Kurs o'quvchilar</h2>
        <div class="flex flex-wrap gap-2">
            <input name="search" value="<?= htmlspecialchars($filters['search'] ?? ''); ?>" class="border rounded px-3 py-2" placeholder="Qidirish...">
            <select name="status" class="border rounded px-3 py-2">
                <option value="">Barcha status</option>
                <option value="active" <?= (($filters['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Faqat Kursda</option>
                <option value="completed" <?= (($filters['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Faqat Tugatgan</option>
            </select>
            <button class="px-4 py-2 border rounded">Filter</button>
        </div>
    </form>

    <div class="bg-white rounded-xl p-4 shadow overflow-auto">
        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="font-semibold text-slate-800">Kurs o'quvchilar ro'yxati</h3>
            <button type="button" id="openCourseStudentBulkModalBtn" class="px-4 py-2 bg-emerald-700 text-white rounded hover:bg-emerald-800">+ Qo'shish</button>
        </div>
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 28%;">
                    <col style="width: 24%;">
                    <col style="width: 14%;">
                    <col style="width: 14%;">
                    <col style="width: 20%;">
                </colgroup>
                <thead><tr><th>FIO</th><th>Kurs</th><th>Xona</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="5" class="text-center text-slate-500">Ma'lumot topilmadi</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $row): ?>
                    <?php
                    $rowJson = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    $isAssigned = !empty($row['id']);
                    $isCompleted = ($row['status'] ?? 'active') === 'completed';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fio']); ?></td>
                        <td><?= htmlspecialchars($row['course_name'] ?: '-'); ?></td>
                        <td><?= htmlspecialchars($row['room_number'] ?: '-'); ?></td>
                        <td>
                            <?php if (!$isAssigned): ?>
                                <span class="px-2 py-1 rounded text-xs bg-slate-100 text-slate-700">Biriktirilmagan</span>
                            <?php elseif ($isCompleted): ?>
                                <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700">Tugatgan</span>
                            <?php else: ?>
                                <span class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-700">Kursda</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="js-course-student-open px-2 py-1 text-xs border rounded" data-item="<?= $rowJson; ?>">Kurs biriktirish</button>
                                <?php if (!empty($row['id'])): ?>
                                    <button type="button" class="js-course-status-toggle px-2 py-1 text-xs bg-indigo-600 text-white rounded" data-id="<?= (int) $row['id']; ?>" data-status="<?= $isCompleted ? 'active' : 'completed'; ?>">
                                        <?= $isCompleted ? 'Faol qil' : 'Tugatdi'; ?>
                                    </button>
                                    <button type="button" class="js-course-student-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="<?= (int) $row['id']; ?>">Delete</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <div class="mt-3 flex gap-2 flex-wrap">
                <?php for ($i = 1; $i <= (int) $pagination['pages']; $i++): ?>
                    <a class="px-3 py-1 border rounded <?= $i === (int) $pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page' => 'course_students', 'p' => $i, 'search' => $filters['search'] ?? '', 'status' => $filters['status'] ?? ''])); ?>"><?= $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="courseStudentBulkModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6">
        <h3 class="font-bold mb-3">Kurs o'quvchi qo'shish</h3>
        <form id="courseStudentBulkForm" class="form-grid">
            <div class="form-field">
                <div class="flex items-center justify-between gap-2">
                    <span class="form-label">Talabalar</span>
                    <button type="button" id="addCourseStudentSelectBtn" class="h-8 w-8 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 flex items-center justify-center" title="Yana talaba qo'shish">+</button>
                </div>
                <div id="courseStudentSelectors" class="space-y-2"></div>
                <p class="text-xs text-slate-500">Har bir qatorda bitta talaba tanlang. Takror tanlash bloklanadi.</p>
            </div>
            <label class="form-field">
                <span class="form-label">Kurs</span>
                <select name="course_id" class="form-input" required>
                    <option value="">Kurs tanlang</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= (int) $course['id']; ?>"><?= htmlspecialchars($course['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span class="form-label">Xona (ixtiyoriy)</span>
                <select name="room_id" class="form-input">
                    <option value="">Xona tanlang</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= (int) $room['id']; ?>"><?= htmlspecialchars($room['room_number']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeCourseStudentBulkModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Qo'shish</button>
            </div>
        </form>
    </div>
</div>

<div id="courseStudentModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <h3 class="font-bold mb-3">Kurs biriktirish</h3>
        <form id="courseStudentForm" class="form-grid">
            <input type="hidden" name="student_id" id="courseStudentId">
            <label class="form-field">
                <span class="form-label">Talaba</span>
                <input id="courseStudentName" class="form-input bg-slate-50" readonly>
            </label>
            <label class="form-field">
                <span class="form-label">Kurs</span>
                <select name="course_id" id="courseSelect" class="form-input" required>
                    <option value="">Kurs tanlang</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= (int) $course['id']; ?>"><?= htmlspecialchars($course['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span class="form-label">Xona</span>
                <select name="room_id" id="courseRoomSelect" class="form-input">
                    <option value="">Xona tanlang</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= (int) $room['id']; ?>"><?= htmlspecialchars($room['room_number']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="flex justify-end gap-2"><button type="button" onclick="closeCourseStudentModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
