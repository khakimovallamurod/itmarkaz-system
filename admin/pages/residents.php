<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? ['search' => '', 'status' => ''];
$rooms = $pageOptions['rooms'] ?? [];
?>
<div class="p-4 space-y-4">
    <form method="get" class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <input type="hidden" name="page" value="residents">
        <div class="flex flex-wrap gap-2">
            <input name="search" value="<?= htmlspecialchars($filters['search'] ?? ''); ?>" class="border rounded px-3 py-2" placeholder="Qidirish...">
            <select name="status" class="border rounded px-3 py-2">
                <option value="">Barchasi</option>
                <option value="assigned" <?= (($filters['status'] ?? '') === 'assigned') ? 'selected' : ''; ?>>Xona berilgan</option>
                <option value="unassigned" <?= (($filters['status'] ?? '') === 'unassigned') ? 'selected' : ''; ?>>Xona berilmagan</option>
            </select>
            <button class="px-4 py-2 border rounded">Filter</button>
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
                    <col style="width: 34%;">
                    <col style="width: 20%;">
                    <col style="width: 24%;">
                    <col style="width: 22%;">
                </colgroup>
                <thead><tr><th>FIO</th><th>Xona</th><th>Kompyuter</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="4" class="text-center text-slate-500">Ma'lumot topilmadi</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $item): ?>
                    <?php $itemJson = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
                    <tr>
                        <td><?= htmlspecialchars($item['fio']); ?></td>
                        <td><?= htmlspecialchars($item['room_number'] ?: '-'); ?></td>
                        <td><?= htmlspecialchars($item['computer_number'] ?: '-'); ?></td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="js-resident-open px-2 py-1 text-xs border rounded" data-item="<?= $itemJson; ?>">Xona berish</button>
                                <?php if (!empty($item['id'])): ?>
                                    <button type="button" class="js-resident-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="<?= (int) $item['id']; ?>">Delete</button>
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
