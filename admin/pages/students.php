<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? ['search' => '', 'direction_id' => 0];
$directions = $pageOptions['directions'] ?? [];
$statuses = $pageOptions['statuses'] ?? [];

function sortIcon($field, $filters) {
    $currentSort = $filters['sort_by'] ?? '';
    $currentOrder = $filters['sort_order'] ?? '';
    if ($currentSort !== $field) return '<i class="fa-solid fa-sort text-slate-300 ml-1 text-[10px]"></i>';
    return $currentOrder === 'ASC' 
        ? '<i class="fa-solid fa-sort-up text-emerald-600 ml-1"></i>' 
        : '<i class="fa-solid fa-sort-down text-emerald-600 ml-1"></i>';
}

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
    <form method="get" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col md:flex-row gap-4 items-center">
        <input type="hidden" name="page" value="students">
        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($filters['sort_by'] ?? ''); ?>">
        <input type="hidden" name="sort_order" value="<?= htmlspecialchars($filters['sort_order'] ?? ''); ?>">

        <div class="relative w-full md:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input name="search" value="<?= htmlspecialchars($filters['search'] ?? ''); ?>" 
                   class="w-full pl-10 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" 
                   placeholder="Ism yoki guruh bo'yicha...">
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <select name="direction_id" class="flex-1 md:w-60 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none transition-all">
                <option value="">Barcha yo'nalishlar</option>
                <?php foreach ($directions as $direction): ?>
                    <option value="<?= (int) $direction['id']; ?>" <?= ((int) ($filters['direction_id'] ?? 0) === (int) $direction['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($direction['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="h-10 px-4 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition-all shadow-sm flex items-center gap-2 text-sm font-medium">
                <i class="fa-solid fa-filter"></i>
                <span class="hidden sm:inline">Filter</span>
            </button>

            <?php if (!empty($filters['search']) || !empty($filters['direction_id'])): ?>
                <a href="index.php?page=students" class="h-10 w-10 flex items-center justify-center bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-all border border-rose-100" title="Tozalash">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="md:ml-auto">
            <button type="button" onclick="openStudentModal()" class="w-full md:w-auto px-5 py-2.5 bg-emerald-600 text-white rounded-xl shadow-sm hover:bg-emerald-700 hover:shadow-emerald-200 transition-all flex items-center justify-center gap-2 font-semibold text-sm">
                <i class="fa-solid fa-user-plus"></i>
                Talaba qo'shish
            </button>
        </div>
    </form>

    <div class="bg-white rounded-xl p-4 shadow overflow-auto">
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 5%;">
                    <col style="width: 20%;">
                    <col style="width: 15%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 14%;">
                    <col style="width: 14%;">
                    <col style="width: 12%;">
                </colgroup>
                <thead><tr>
                    <th>#</th>
                    <th data-sort="s.fio">FIO <?= sortIcon('s.fio', $filters); ?></th>
                    <th data-sort="d.name">Yo'nalish <?= sortIcon('d.name', $filters); ?></th>
                    <th data-sort="s.guruh">Guruh <?= sortIcon('s.guruh', $filters); ?></th>
                    <th data-sort="s.kirgan_yili">Kurs <?= sortIcon('s.kirgan_yili', $filters); ?></th>
                    <th>Telefon</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="8" class="text-center text-slate-500 py-10">Ma'lumot topilmadi</td></tr>
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
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($item['yonalish']); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($item['guruh']); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($courseLabel((int) $item['kirgan_yili'])); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($item['telefon']); ?></td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <?php foreach ($item['statuses'] as $st): ?>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-semibold"><?= htmlspecialchars($st); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="js-student-edit h-8 w-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors" data-item="<?= $itemJson; ?>">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button type="button" class="js-student-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" data-id="<?= (int) $item['id']; ?>">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Stats -->
        <?php $stats = $pageData['stats'] ?? []; ?>
        <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-100 flex flex-wrap gap-6 text-sm">
            <div class="flex items-center gap-2">
                <span class="text-slate-500">Jami talabalar:</span>
                <span class="font-bold text-slate-800"><?= (int) ($stats['total'] ?? 0); ?></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-slate-500">Kurs o'quvchilar:</span>
                <span class="font-bold text-emerald-600"><?= (int) ($stats['course_students'] ?? 0); ?></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-slate-500">Rezidentlar:</span>
                <span class="font-bold text-blue-600"><?= (int) ($stats['residents'] ?? 0); ?></span>
            </div>
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
