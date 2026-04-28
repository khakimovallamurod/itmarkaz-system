<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
?>
<div class="p-4">
    <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-center justify-between mb-3"><h3 class="font-semibold">Statuslar ro'yxati</h3><button onclick="openStatusModal()" class="bg-emerald-800 text-white px-4 py-2 rounded">+ Qo'shish</button></div>
        <div class="table-shell">
            <table class="admin-table"><colgroup><col style="width:72%;"><col style="width:28%;"></colgroup><thead><tr><th>Nomi</th><th>Actions</th></tr></thead><tbody>
            <?php if (!$items): ?><tr><td colspan="2" class="text-center text-slate-500">Ma'lumot topilmadi</td></tr><?php endif; ?>
            <?php foreach($items as $s): ?><?php $json = htmlspecialchars(json_encode($s, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?><tr><td><?= htmlspecialchars($s['name']); ?></td><td><div class="table-actions"><button type="button" class="js-status-edit px-2 py-1 text-xs border rounded" data-item="<?= $json; ?>">Edit</button><button type="button" class="js-status-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="<?= (int)$s['id']; ?>">Delete</button></div></td></tr><?php endforeach; ?>
            </tbody></table>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?><div class="mt-3 flex gap-2 flex-wrap"><?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?><a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page'=>'statuses','p'=>$i])); ?>"><?= $i; ?></a><?php endfor; ?></div><?php endif; ?>
    </div>
</div>
<div id="statusModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4"><div class="admin-modal-panel bg-white rounded-2xl shadow-xl p-6 w-full max-w-md"><h3 class="font-bold mb-3">Status</h3><form id="statusForm" class="form-grid"><input type="hidden" name="id" id="statusId"><label class="form-field"><span class="form-label">Status nomi</span><input name="name" id="statusName" class="form-input" placeholder="Masalan: Rezident" required></label><div class="flex justify-end gap-2"><button type="button" onclick="closeStatusModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div></form></div></div>
