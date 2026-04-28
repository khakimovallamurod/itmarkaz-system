<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? ['search' => '', 'direction_id' => 0];
$directions = $pageOptions['directions'] ?? [];
$statuses = $pageOptions['statuses'] ?? [];

$currentYear = (int) date('Y');
$courseLabel = static function (int $entryYear) use ($currentYear): string {
    if ($entryYear < 1900) return '-';
    $elapsed = $currentYear - $entryYear;
    if ($elapsed < 0) return 'Hali boshlanmagan';
    if ($elapsed > 4) return 'Bitirgan';
    return (($elapsed === 0 ? 1 : $elapsed) . '-kurs');
};
?>
<div class="p-4 space-y-4">
    <form method="get" class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <input type="hidden" name="page" value="students">
        <div class="flex gap-2 flex-wrap">
            <input name="search" value="<?= htmlspecialchars($filters['search'] ?? ''); ?>" class="border rounded px-3 py-2" placeholder="Qidirish...">
            <select name="direction_id" class="border rounded px-3 py-2">
                <option value="">Barcha yo'nalishlar</option>
                <?php foreach ($directions as $direction): ?>
                    <option value="<?= (int) $direction['id']; ?>" <?= ((int) ($filters['direction_id'] ?? 0) === (int) $direction['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($direction['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="px-4 py-2 border rounded">Filter</button>
        </div>
        <button type="button" onclick="openStudentModal()" class="px-4 py-2 bg-emerald-800 text-white rounded">+ Talaba qo'shish</button>
    </form>

    <div class="bg-white rounded-xl p-4 shadow overflow-auto">
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 16%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 14%;">
                    <col style="width: 18%;">
                    <col style="width: 12%;">
                </colgroup>
                <thead><tr><th>FIO</th><th>Yo'nalish</th><th>Guruh</th><th>Kurs</th><th>Telefon</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="7" class="text-center text-slate-500">Ma'lumot topilmadi</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $item): ?>
                    <?php $itemJson = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
                    <tr>
                        <td><?= htmlspecialchars($item['fio']); ?></td>
                        <td><?= htmlspecialchars($item['yonalish']); ?></td>
                        <td><?= htmlspecialchars($item['guruh']); ?></td>
                        <td><?= htmlspecialchars($courseLabel((int) $item['kirgan_yili'])); ?></td>
                        <td><?= htmlspecialchars($item['telefon']); ?></td>
                        <td><?= htmlspecialchars(implode(', ', $item['statuses'] ?? [])); ?></td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="js-student-edit px-2 py-1 text-xs border rounded" data-item="<?= $itemJson; ?>">Edit</button>
                                <button type="button" class="js-student-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="<?= (int) $item['id']; ?>">Delete</button>
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
                    <a
                        class="px-3 py-1 border rounded <?= $i === (int) $pagination['page'] ? 'bg-green-600 text-white' : ''; ?>"
                        href="?<?= htmlspecialchars(http_build_query(['page' => 'students', 'p' => $i, 'search' => $filters['search'] ?? '', 'direction_id' => $filters['direction_id'] ?? ''])); ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="studentModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-2xl p-5">
        <h3 id="studentModalTitle" class="font-bold mb-3">Talaba qo'shish</h3>
        <form id="studentForm" class="form-grid md:grid-cols-2">
            <input type="hidden" name="id" id="studentId">
            <label class="form-field">
                <span class="form-label">FIO</span>
                <input name="fio" placeholder="Masalan: Ali Valiyev" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Yo'nalish</span>
                <select name="yonalish_id" id="studentDirectionSelect" class="form-input" required>
                    <option value="">Yo'nalish tanlang</option>
                    <?php foreach ($directions as $direction): ?>
                        <option value="<?= (int) $direction['id']; ?>"><?= htmlspecialchars($direction['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span class="form-label">Guruh</span>
                <input name="guruh" placeholder="Masalan: FE-12" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Kirgan yili</span>
                <input type="number" name="kirgan_yili" id="studentEntryYear" placeholder="Masalan: 2024" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Kurs (avtomatik)</span>
                <input type="text" id="studentAutoCourse" class="form-input bg-slate-50" readonly>
            </label>
            <label class="form-field">
                <span class="form-label">Telefon</span>
                <input name="telefon" data-phone-input placeholder="+998 90 123 45 67" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Telegram Chat ID</span>
                <input name="telegram_chat_id" placeholder="Masalan: 12345678" class="form-input">
            </label>
            <div class="md:col-span-2">
                <label class="form-label">Statuslar</label>
                <div id="studentStatusCheckboxes" class="mt-2 grid sm:grid-cols-2 gap-2 rounded-lg border border-slate-200 p-3 bg-slate-50">
                    <?php foreach ($statuses as $status): ?>
                        <label class="flex items-center gap-2 cursor-pointer select-none rounded-md px-2 py-1.5 hover:bg-white">
                            <input type="checkbox" name="status[]" value="<?= (int) $status['id']; ?>" class="h-4 w-4 accent-green-600">
                            <span class="text-sm text-slate-700"><?= htmlspecialchars($status['name']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" onclick="closeStudentModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button>
            </div>
        </form>
    </div>
</div>
