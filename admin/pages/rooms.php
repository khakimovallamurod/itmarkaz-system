<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? [];

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
    <form method="get" class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <input type="hidden" name="page" value="rooms">
        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($filters['sort_by'] ?? ''); ?>">
        <input type="hidden" name="sort_order" value="<?= htmlspecialchars($filters['sort_order'] ?? ''); ?>">
        <h3 class="font-semibold">Xonalar</h3>
        <button type="button" onclick="openRoomModal()" class="bg-emerald-800 text-white px-4 py-2 rounded">+ Xona qo'shish</button>
    </form>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 overflow-auto">
        <div class="table-shell border-none">
            <table class="admin-table w-full">
                <colgroup>
                    <col style="width: 60px;">
                    <col style="width: 30%;">
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                </colgroup>
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider" data-sort="room_number">Xona <?= sortIcon('room_number', $filters); ?></th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider" data-sort="capacity">Sig'im <?= sortIcon('capacity', $filters); ?></th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider" data-sort="computers_count">Kompyuter <?= sortIcon('computers_count', $filters); ?></th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$items): ?><tr><td colspan="5" class="text-center text-slate-500">Ma'lumot topilmadi</td></tr><?php endif; ?>
                <?php 
                $totalCount = $pagination['total'] ?? 0;
                $offset = $pagination['offset'] ?? 0;
                foreach ($items as $index => $r): 
                    $rowNum = $totalCount - $offset - $index;
                ?>
                    <?php $rowJson = htmlspecialchars(json_encode($r, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
                    <tr>
                        <td class="px-4 py-3 text-slate-400 font-mono text-xs"><?= $rowNum; ?></td>
                        <td class="px-4 py-3 font-medium text-slate-700"><?= htmlspecialchars($r['room_number']); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= (int) $r['capacity']; ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= (int) $r['computers_count']; ?></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="js-room-edit h-8 w-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors" data-item="<?= $rowJson; ?>">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button type="button" class="js-room-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" data-id="<?= (int) $r['id']; ?>">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?><div class="mt-3 flex gap-2 flex-wrap"><?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?><a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page'=>'rooms','p'=>$i])); ?>"><?= $i; ?></a><?php endfor; ?></div><?php endif; ?>
    </div>
</div>

<div id="roomModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
        <h3 class="font-bold mb-3">Xona</h3>
        <form id="roomForm" class="form-grid">
            <input type="hidden" name="id">
            <label class="form-field"><span class="form-label">Xona raqami</span><input name="room_number" class="form-input" placeholder="Masalan: 204" required></label>
            <label class="form-field"><span class="form-label">Sig'im</span><input type="number" name="capacity" class="form-input" placeholder="Masalan: 20" required></label>
            <label class="form-field"><span class="form-label">Kompyuter soni</span><input type="number" name="computers_count" class="form-input" placeholder="Masalan: 15" required></label>
            <div class="flex justify-end gap-2"><button type="button" onclick="closeRoomModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
