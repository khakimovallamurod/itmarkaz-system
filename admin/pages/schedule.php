<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? ['type' => ''];
$type = $filters['type'] ?? '';
?>
<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 justify-between items-center">
        <h2 class="font-semibold">Ish jadvali</h2>
        <div class="flex flex-wrap gap-2">
            <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-1">
                <a href="?<?= htmlspecialchars(http_build_query(['page' => 'schedule'])); ?>" class="px-3 py-1.5 rounded-md text-sm <?= $type === '' ? 'bg-white shadow' : ''; ?>">Barchasi</a>
                <a href="?<?= htmlspecialchars(http_build_query(['page' => 'schedule', 'type' => 'daily'])); ?>" class="px-3 py-1.5 rounded-md text-sm <?= $type === 'daily' ? 'bg-white shadow' : ''; ?>">Kunlik</a>
                <a href="?<?= htmlspecialchars(http_build_query(['page' => 'schedule', 'type' => 'weekly'])); ?>" class="px-3 py-1.5 rounded-md text-sm <?= $type === 'weekly' ? 'bg-white shadow' : ''; ?>">Haftalik</a>
            </div>
            <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-1">
                <button type="button" id="scheduleViewListBtn" class="px-3 py-1.5 rounded-md text-sm bg-white shadow">List</button>
                <button type="button" id="scheduleViewGridBtn" class="px-3 py-1.5 rounded-md text-sm">Jadval</button>
            </div>
            <button onclick="openScheduleModal()" class="px-4 py-2 bg-emerald-800 text-white rounded">+ Jadval qo'shish</button>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow overflow-auto">
        <div class="table-shell" id="scheduleTableWrap">
            <table class="admin-table">
                <colgroup><col style="width: 18%;"><col style="width: 44%;"><col style="width: 18%;"><col style="width: 20%;"></colgroup>
                <thead><tr><th>Type</th><th>Sarlavha</th><th>Sana</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$items): ?><tr><td colspan="4" class="text-center text-slate-500">Ma'lumot topilmadi</td></tr><?php endif; ?>
                <?php foreach ($items as $item): ?>
                    <?php $json = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
                    <tr><td><?= $item['type'] === 'daily' ? 'Kunlik' : 'Haftalik'; ?></td><td><?= htmlspecialchars($item['title']); ?></td><td><?= htmlspecialchars($item['date']); ?></td><td><div class="table-actions"><button type="button" class="js-schedule-edit px-2 py-1 text-xs border rounded" data-item="<?= $json; ?>">Edit</button><button type="button" class="js-schedule-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="<?= (int)$item['id']; ?>">Delete</button></div></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="scheduleGrid" class="hidden grid md:grid-cols-2 xl:grid-cols-3 gap-3">
            <?php foreach ($items as $item): ?>
                <?php $json = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
                <article class="rounded-xl border border-slate-200 p-3 bg-slate-50"><div class="flex items-start justify-between gap-2"><p class="font-medium text-slate-900"><?= htmlspecialchars($item['title']); ?></p><span class="text-xs px-2 py-1 rounded <?= $item['type'] === 'daily' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'; ?>"><?= $item['type'] === 'daily' ? 'Kunlik' : 'Haftalik'; ?></span></div><p class="text-sm text-slate-600 mt-2"><?= htmlspecialchars($item['date']); ?></p><div class="flex gap-2 mt-3"><button type="button" class="js-schedule-edit px-2 py-1 text-xs border rounded" data-item="<?= $json; ?>">Edit</button><button type="button" class="js-schedule-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="<?= (int)$item['id']; ?>">Delete</button></div></article>
            <?php endforeach; ?>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?><div class="mt-3 flex gap-2 flex-wrap"><?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?><a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page'=>'schedule','type'=>$type,'p'=>$i])); ?>"><?= $i; ?></a><?php endfor; ?></div><?php endif; ?>
    </div>
</div>

<div id="scheduleModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-lg p-4">
        <h3 class="font-bold mb-3">Jadval</h3>
        <form id="scheduleForm" class="form-grid">
            <input type="hidden" name="id">
            <label class="form-field"><span class="form-label">Turi</span><select name="type" class="form-input" required><option value="weekly">Haftalik</option><option value="daily">Kunlik</option></select></label>
            <label class="form-field"><span class="form-label">Sarlavha</span><input name="title" placeholder="Sarlavha" class="form-input" required></label>
            <label class="form-field"><span class="form-label">Sana</span><input type="date" name="date" class="form-input" required></label>
            <div class="flex justify-end gap-2"><button type="button" onclick="closeScheduleModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
