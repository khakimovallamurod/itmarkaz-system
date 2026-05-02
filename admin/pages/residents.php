<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1, 'total' => 0, 'offset' => 0];
$filters = $pageData['filters'] ?? [];
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
        <input type="hidden" name="page" value="residents">
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
                <option value="">Barcha holatlar</option>
                <option value="assigned" <?= (($filters['status'] ?? '') === 'assigned') ? 'selected' : ''; ?>>Xona berilgan</option>
                <option value="unassigned" <?= (($filters['status'] ?? '') === 'unassigned') ? 'selected' : ''; ?>>Xona berilmagan</option>
            </select>

            <button type="submit" class="h-10 px-5 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition-all shadow-sm flex items-center gap-2 text-sm font-medium">
                <i class="fa-solid fa-filter"></i>
                <span class="hidden sm:inline">Filter</span>
            </button>

            <?php if (!empty($filters['search']) || !empty($filters['status'])): ?>
                <a href="index.php?page=residents" class="h-10 w-10 flex items-center justify-center bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-all border border-rose-100" title="Tozalash">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="bg-white rounded-xl p-4 shadow overflow-auto">
        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="font-semibold text-slate-800">Rezidentlar ro'yxati</h3>
            <button type="button" id="openResidentBulkModalBtn" class="px-4 py-2 bg-emerald-700 text-white rounded hover:bg-emerald-800">+ Qo'shish</button>
        </div>
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 5%;">
                    <col style="width: 30%;">
                    <col style="width: 20%;">
                    <col style="width: 24%;">
                    <col style="width: 21%;">
                </colgroup>
                <thead><tr>
                    <th>#</th>
                    <th data-sort="s.fio" class="cursor-pointer">FIO <?= sortIcon('s.fio', $filters); ?></th>
                    <th data-sort="rm.room_number" class="cursor-pointer">Xona <?= sortIcon('rm.room_number', $filters); ?></th>
                    <th data-sort="r.computer_number" class="cursor-pointer">Kompyuter <?= sortIcon('r.computer_number', $filters); ?></th>
                    <th class="text-right">Amallar</th>
                </tr></thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="5" class="text-center text-slate-500">Ma'lumot topilmadi</td></tr>
                <?php endif; ?>
                <?php 
                $totalCount = $pagination['total'] ?? 0;
                $offset = $pagination['offset'] ?? 0;
                foreach ($items as $index => $item): 
                    $rowNum = $totalCount - $offset - $index;
                    $itemJson = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); 
                ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-slate-400 font-mono text-xs"><?= $rowNum; ?></td>
                        <td class="px-4 py-3 font-medium text-slate-700"><?= htmlspecialchars($item['fio']); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($item['room_number'] ?: '-'); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($item['computer_number'] ?: '-'); ?></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="js-resident-open h-8 w-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors" data-item="<?= $itemJson; ?>" title="Xona biriktirish">
                                    <i class="fa-solid fa-door-open text-xs"></i>
                                </button>
                                <?php if (!empty($item['id'])): ?>
                                    <button type="button" class="js-resident-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" data-id="<?= (int) $item['id']; ?>" title="Rezidentlikni bekor qilish">
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
                    <a class="px-3 py-1 border rounded <?= $i === (int) $pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page' => 'residents', 'p' => $i, 'search' => $filters['search'] ?? '', 'status' => $filters['status'] ?? ''])); ?>"><?= $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="residentBulkModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6">
        <h3 class="font-bold mb-3">Rezident qo'shish</h3>
        <form id="residentBulkForm" class="form-grid">
            <div class="form-field">
                <div class="flex items-center justify-between gap-2">
                    <span class="form-label">Talabalar</span>
                    <button type="button" id="addResidentStudentSelectBtn" class="h-8 w-8 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 flex items-center justify-center" title="Yana talaba qo'shish">+</button>
                </div>
                <div id="residentStudentSelectors" class="space-y-2"></div>
                <p class="text-xs text-slate-500">Har bir qatorda bitta talaba tanlang. Takror tanlash bloklanadi.</p>
            </div>
            <label class="form-field">
                <span class="form-label">Xona (ixtiyoriy)</span>
                <select name="room_id" class="form-input">
                    <option value="">Xona tanlang</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= (int) $room['id']; ?>"><?= htmlspecialchars($room['room_number']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span class="form-label">Kompyuter raqami (ixtiyoriy)</span>
                <input name="computer_number" placeholder="Masalan: PC-07" class="form-input">
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeResidentBulkModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Qo'shish</button>
            </div>
        </form>
    </div>
</div>

<div id="residentModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <h3 class="font-bold mb-3">Rezidentga xona berish</h3>
        <form id="residentForm" class="form-grid">
            <input type="hidden" name="student_id" id="residentStudentId">
            <label class="form-field">
                <span class="form-label">Talaba</span>
                <input id="residentStudentName" class="form-input bg-slate-50" readonly>
            </label>
            <label class="form-field">
                <span class="form-label">Xona</span>
                <select name="room_id" id="residentRoomSelect" class="form-input">
                    <option value="">Xona tanlang</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= (int) $room['id']; ?>"><?= htmlspecialchars($room['room_number']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span class="form-label">Kompyuter raqami</span>
                <input name="computer_number" placeholder="Masalan: PC-07" class="form-input">
            </label>
            <div class="flex justify-end gap-2"><button type="button" onclick="closeResidentModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
