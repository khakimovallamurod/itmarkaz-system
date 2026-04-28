<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
?>
<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <div><h2 class="font-semibold">Tanlovlar</h2><p class="text-xs text-slate-500">Card ko'rinish, detail sahifa, xabar yuborish va natija boshqaruvi</p></div>
        <button onclick="openCompetitionModal()" class="px-4 py-2 bg-emerald-800 text-white rounded">+ Tanlov qo'shish</button>
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php if (!$items): ?><p class="text-sm text-slate-500">Tanlovlar topilmadi.</p><?php endif; ?>
        <?php foreach ($items as $item): ?>
            <?php $data = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
            <article class="group rounded-xl bg-white p-4 shadow hover:shadow-lg border border-slate-100 transition-all duration-200 hover:-translate-y-1 cursor-pointer" data-open-competition="<?= (int) $item['id']; ?>">
                <div class="flex items-start justify-between gap-2"><h3 class="font-semibold text-slate-900 leading-snug"><?= htmlspecialchars($item['name']); ?></h3><span class="text-[11px] px-2 py-1 rounded bg-emerald-100 text-emerald-700"><?= htmlspecialchars($item['competition_date']); ?></span></div>
                <?php $desc = (string) ($item['description'] ?? ''); $shortDesc = strlen($desc) > 120 ? substr($desc, 0, 120) . '...' : $desc; ?>
                <p class="text-sm text-slate-600 mt-2"><?= htmlspecialchars($shortDesc); ?></p>
                <p class="text-xs text-slate-500 mt-2"><i class="fa-solid fa-location-dot mr-1"></i><?= htmlspecialchars($item['location'] ?: 'Manzil kiritilmagan'); ?></p>
                <div class="flex items-center justify-between mt-3 text-xs text-slate-500"><span>Ishtirokchi: <?= (int) $item['participant_count']; ?></span><span>Natija: <?= (int) $item['result_count']; ?></span></div>
                <div class="flex gap-2 mt-4" data-competition-actions><button type="button" class="js-competition-edit px-2 py-1 text-xs border rounded" data-item="<?= $data; ?>">Edit</button><button type="button" class="js-competition-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="<?= (int) $item['id']; ?>">Delete</button></div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (($pagination['pages'] ?? 1) > 1): ?>
        <div class="flex gap-2 flex-wrap">
            <?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?>
                <a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page' => 'competitions', 'p' => $i])); ?>"><?= $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<div id="competitionModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-xl p-4">
        <h3 class="font-bold mb-3">Tanlov</h3>
        <form id="competitionForm" class="form-grid">
            <input type="hidden" name="id">
            <label class="form-field"><span class="form-label">Nomi</span><input name="name" placeholder="Nomi" class="form-input" required></label>
            <label class="form-field"><span class="form-label">Tavsif</span><textarea name="description" placeholder="Qisqacha tavsif" class="form-input"></textarea></label>
            <label class="form-field"><span class="form-label">Manzil</span><input name="location" placeholder="Masalan: IT Park, 3-zal" class="form-input"></label>
            <label class="form-field"><span class="form-label">Ro'yxat deadline</span><input type="date" name="registration_deadline" class="form-input" required></label>
            <label class="form-field"><span class="form-label">Tanlov sanasi</span><input type="date" name="competition_date" class="form-input" required></label>
            <div class="flex justify-end gap-2"><button type="button" onclick="closeCompetitionModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
