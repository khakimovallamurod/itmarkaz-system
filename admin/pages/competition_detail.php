<?php
$detail = $pageData ?? [];
$competition = $detail['competition'] ?? null;
$participants = $detail['participants'] ?? [];
$results = $detail['results'] ?? [];
$studentOptions = $pageOptions['student_options'] ?? [];
$competitionId = (int) ($_GET['id'] ?? 0);
?>
<div class="p-4 space-y-4" data-competition-id="<?= $competitionId; ?>" id="competitionDetailPage">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-3 justify-between items-center">
        <div>
            <p class="text-xs text-slate-500 mb-1">Tanlov tafsiloti</p>
            <h2 id="competitionDetailName" class="font-semibold text-xl"><?= htmlspecialchars($competition['name'] ?? 'Tanlov topilmadi'); ?></h2>
            <p id="competitionDetailMeta" class="text-sm text-slate-600 mt-1">
                <?php if ($competition): ?>
                    <?= htmlspecialchars($competition['competition_date']); ?> | <?= htmlspecialchars($competition['location'] ?: 'Manzil yo\'q'); ?> | Deadline: <?= htmlspecialchars($competition['registration_deadline']); ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="index.php?page=competitions" class="px-4 py-2 border rounded">Orqaga</a>
            <?php if ($competition): ?>
            <button id="competitionNotifyBtn" class="px-4 py-2 bg-indigo-700 text-white rounded">Talabalarga xabar yuborish</button>
            <button id="competitionResultBtn" class="px-4 py-2 bg-emerald-800 text-white rounded">Natija kiritish</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($competition): ?>
    <div class="grid lg:grid-cols-2 gap-4">
        <section class="bg-white rounded-xl p-4 shadow">
            <div class="flex justify-between items-center mb-3"><h3 class="font-semibold">Ishtirokchilar</h3><button id="competitionParticipantAddBtn" class="text-xs bg-emerald-800 text-white px-3 py-1 rounded">+ Qo'shish</button></div>
            <ul id="competitionParticipantList" class="space-y-2">
                <?php if (!$participants): ?><li class="text-sm text-slate-500">Ishtirokchilar hali qo'shilmagan.</li><?php endif; ?>
                <?php foreach ($participants as $item): ?>
                    <li class="rounded-lg border border-slate-200 px-3 py-2 flex items-center justify-between"><span class="text-sm"><?= htmlspecialchars($item['fio']); ?></span><button type="button" class="js-participant-delete text-xs px-2 py-1 rounded bg-red-500 text-white" data-id="<?= (int)$item['id']; ?>">O'chirish</button></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <section class="bg-white rounded-xl p-4 shadow"><h3 class="font-semibold mb-3">Natijalar</h3><div id="competitionResultCards" class="space-y-2"><?php if (!$results): ?><p class="text-sm text-slate-500">Natijalar kiritilmagan.</p><?php endif; ?><?php foreach($results as $item): ?><article class="rounded-lg border border-slate-200 px-3 py-2 bg-slate-50"><p class="text-sm font-medium text-slate-900"><?= (int)$item['position']; ?>-o'rin: <?= htmlspecialchars($item['fio']); ?></p></article><?php endforeach; ?></div></section>
    </div>
    <?php endif; ?>
</div>

<div id="competitionNotifyModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-xl p-4">
        <h3 class="font-bold mb-3">Talabalarga yuborish</h3>
        <form id="competitionNotifyForm" class="form-grid">
            <input type="hidden" name="competition_id" id="notifyCompetitionId" value="<?= $competitionId; ?>">
            <div class="form-field"><div class="flex items-center justify-between gap-2"><span class="form-label">Talabalar</span><button type="button" id="addNotifyStudentSelectBtn" class="h-8 w-8 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 flex items-center justify-center" title="Yana talaba qo'shish">+</button></div><div id="notifyStudentSelectors" class="space-y-2"></div><p class="text-xs text-slate-500">Har bir qatorda bitta talaba tanlang. Takror tanlashga ruxsat berilmaydi.</p></div>
            <label class="form-field"><span class="form-label">Xabar</span><textarea name="message" class="form-input" rows="4" placeholder="Xabar matni" required></textarea></label>
            <div class="flex justify-end gap-2"><button type="button" class="px-4 py-2 border rounded" onclick="closeCompetitionNotifyModal()">Bekor</button><button class="px-4 py-2 bg-indigo-700 text-white rounded">Yuborish</button></div>
        </form>
    </div>
</div>

<div id="competitionResultModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4"><div class="admin-modal-panel bg-white rounded-xl w-full max-w-lg p-4"><h3 class="font-bold mb-3">Natija kiritish</h3><form id="competitionResultForm" class="form-grid"><input type="hidden" name="competition_id" value="<?= $competitionId; ?>"><label class="form-field"><span class="form-label">Talaba</span><select id="competitionResultStudentSelect" name="student_id" class="form-input" required><option value="">Talabani tanlang</option><?php foreach($studentOptions as $s): ?><option value="<?= (int)$s['id']; ?>"><?= htmlspecialchars($s['fio']); ?></option><?php endforeach; ?></select></label><label class="form-field"><span class="form-label">O'rin</span><select name="position" class="form-input" required><option value="1">1-o'rin</option><option value="2">2-o'rin</option><option value="3">3-o'rin</option></select></label><div class="flex justify-end gap-2"><button type="button" class="px-4 py-2 border rounded" onclick="closeCompetitionResultModal()">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div></form></div></div>

<div id="competitionParticipantModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4"><div class="admin-modal-panel bg-white rounded-xl w-full max-w-lg p-4"><h3 class="font-bold mb-3">Ishtirokchi qo'shish</h3><form id="competitionParticipantForm" class="form-grid"><input type="hidden" name="competition_id" value="<?= $competitionId; ?>"><label class="form-field"><span class="form-label">Talaba</span><select id="competitionParticipantStudentSelect" name="student_id" class="form-input" required><option value="">Talabani tanlang</option><?php foreach($studentOptions as $s): ?><option value="<?= (int)$s['id']; ?>"><?= htmlspecialchars($s['fio']); ?></option><?php endforeach; ?></select></label><div class="flex justify-end gap-2"><button type="button" class="px-4 py-2 border rounded" onclick="closeCompetitionParticipantModal()">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Qo'shish</button></div></form></div></div>
