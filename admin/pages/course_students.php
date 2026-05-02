<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? ['search' => '', 'status' => ''];
$courses = $pageOptions['courses'] ?? [];
$rooms = $pageOptions['rooms'] ?? [];

function sortIcon($field, $filters) {
    $currentSort = $filters['sort_by'] ?? '';
    $currentOrder = $filters['sort_order'] ?? '';
    if ($currentSort !== $field) return '<i class="fa-solid fa-sort text-slate-300 ml-1 text-[10px]"></i>';
    return $currentOrder === 'ASC' 
        ? '<i class="fa-solid fa-sort-up text-emerald-600 ml-1"></i>' 
        : '<i class="fa-solid fa-sort-down text-emerald-600 ml-1"></i>';
}
?>
<div class="p-4 space-y-4">
    <form method="get" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col md:flex-row gap-4 items-center">
        <input type="hidden" name="page" value="course_students">
        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($filters['sort_by'] ?? ''); ?>">
        <input type="hidden" name="sort_order" value="<?= htmlspecialchars($filters['sort_order'] ?? ''); ?>">

        <div class="relative w-full md:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input name="search" value="<?= htmlspecialchars($filters['search'] ?? ''); ?>" 
                   class="w-full pl-10 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" 
                   placeholder="Ism bo'yicha qidirish...">
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <select name="status" class="flex-1 md:w-60 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none transition-all">
                <option value="">Barcha statuslar</option>
                <option value="active" <?= (($filters['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Faqat Kursda</option>
                <option value="completed" <?= (($filters['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Faqat Tugatgan</option>
            </select>

            <button type="submit" class="h-10 px-5 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition-all shadow-sm flex items-center gap-2 text-sm font-medium">
                <i class="fa-solid fa-filter"></i>
                <span class="hidden sm:inline">Filter</span>
            </button>

            <?php if (!empty($filters['search']) || !empty($filters['status'])): ?>
                <a href="index.php?page=course_students" class="h-10 w-10 flex items-center justify-center bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-all border border-rose-100" title="Tozalash">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                </a>
            <?php endif; ?>
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
                    <col style="width: 50px;">
                    <col style="width: 25%;">
                    <col style="width: 20%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 250px;">
                </colgroup>
                <thead><tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3 text-left" data-sort="s.fio">FIO <?= sortIcon('s.fio', $filters); ?></th>
                    <th class="px-4 py-3 text-left" data-sort="c.name">Kurs <?= sortIcon('c.name', $filters); ?></th>
                    <th class="px-4 py-3 text-left text-xs" data-sort="r.room_number">Xona <?= sortIcon('r.room_number', $filters); ?></th>
                    <th class="px-4 py-3 text-left text-xs" data-sort="cs.status">Status <?= sortIcon('cs.status', $filters); ?></th>
                    <th class="px-4 py-3 text-right">Amallar</th>
                </tr></thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="6" class="text-center text-slate-500 py-10">Ma'lumot topilmadi</td></tr>
                <?php endif; ?>
                <?php 
                $totalCount = $pagination['total'] ?? 0;
                $offset = $pagination['offset'] ?? 0;
                foreach ($items as $index => $row): 
                    $rowNum = $totalCount - $offset - $index;
                    $rowJson = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    $isAssigned = !empty($row['id']);
                    $isCompleted = ($row['status'] ?? 'active') === 'completed';
                ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-slate-400 font-mono text-xs"><?= $rowNum; ?></td>
                        <td class="px-4 py-3 font-medium text-slate-700"><?= htmlspecialchars($row['fio']); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($row['course_name'] ?: '-'); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($row['room_number'] ?: '-'); ?></td>
                        <td class="px-4 py-3">
                            <?php if (!$isAssigned): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Biriktirilmagan</span>
                            <?php elseif ($isCompleted): ?>
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">Tugatgan</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700">Kursda</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="js-course-student-open h-8 w-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors" data-item="<?= $rowJson; ?>" title="Kurs biriktirish">
                                    <i class="fa-solid fa-graduation-cap text-xs"></i>
                                </button>
                                <?php if (!empty($row['id'])): ?>
                                    <button type="button" class="js-course-status-toggle h-8 w-8 rounded-lg border <?= $isCompleted ? 'border-emerald-100 text-emerald-600 hover:bg-emerald-50' : 'border-blue-100 text-blue-600 hover:bg-blue-50'; ?> flex items-center justify-center transition-colors" data-id="<?= (int) $row['id']; ?>" data-status="<?= $isCompleted ? 'active' : 'completed'; ?>" title="<?= $isCompleted ? 'Faol qilish' : 'Tugatgan deb belgilash'; ?>">
                                        <i class="fa-solid <?= $isCompleted ? 'fa-rotate-left' : 'fa-check-double'; ?> text-xs"></i>
                                    </button>
                                    <button type="button" class="js-course-student-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" data-id="<?= (int) $row['id']; ?>" title="O'quvchini kursdan chiqarish">
                                        <i class="fa-solid fa-user-minus text-xs"></i>
                                    </button>
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
