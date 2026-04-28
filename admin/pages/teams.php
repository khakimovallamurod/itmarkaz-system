<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$studentOptions = $pageOptions['student_options'] ?? [];
?>
<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <div><h2 class="font-semibold">Upwork Jamoa</h2><p class="text-xs text-slate-500">Jamoa yaratish, a'zolarni qo'shish va chiqarish</p></div>
        <button id="openTeamCreateModalBtn" class="px-4 py-2 bg-emerald-800 text-white rounded">+ Jamoa yaratish</button>
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php if (!$items): ?><p class="text-sm text-slate-500">Jamoalar mavjud emas.</p><?php endif; ?>
        <?php foreach ($items as $team): ?>
            <article class="rounded-xl bg-white p-4 shadow border border-slate-100">
                <div class="flex items-start justify-between gap-2"><h3 class="font-semibold text-slate-900"><?= htmlspecialchars($team['team_name']); ?></h3><button type="button" class="js-team-delete text-xs px-2 py-1 rounded bg-red-500 text-white" data-id="<?= (int)$team['id']; ?>">O'chirish</button></div>
                <p class="text-xs text-slate-500 mt-1">A'zolar: <?= count($team['members'] ?? []); ?></p>
                <div class="mt-3 space-y-2 max-h-44 overflow-auto">
                    <?php if (empty($team['members'])): ?><p class="text-sm text-slate-500">Hali a'zo qo'shilmagan</p><?php endif; ?>
                    <?php foreach (($team['members'] ?? []) as $member): ?>
                        <div class="rounded-lg border border-slate-200 px-3 py-2 flex items-center justify-between"><span class="text-sm text-slate-700"><?= htmlspecialchars($member['fio']); ?></span><button type="button" class="js-team-member-remove text-xs px-2 py-1 rounded bg-slate-800 text-white" data-id="<?= (int)$member['id']; ?>">Chiqarish</button></div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="js-team-member-open mt-3 px-3 py-1.5 text-xs rounded bg-emerald-700 text-white" data-team-id="<?= (int)$team['id']; ?>">+ A'zo qo'shish</button>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if (($pagination['pages'] ?? 1) > 1): ?><div class="flex gap-2 flex-wrap"><?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?><a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page'=>'teams','p'=>$i])); ?>"><?= $i; ?></a><?php endfor; ?></div><?php endif; ?>
</div>

<div id="teamModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-xl p-4">
        <h3 class="font-bold mb-3">Jamoa yaratish</h3>
        <form id="teamForm" class="form-grid">
            <label class="form-field"><span class="form-label">Jamoa nomi</span><input name="team_name" class="form-input" placeholder="Masalan: Team Falcon" required></label>
            <div class="form-field">
                <div class="flex items-center justify-between gap-2"><span class="form-label">Talabalar</span><button type="button" id="addTeamStudentSelectBtn" class="h-8 w-8 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 flex items-center justify-center" title="Yana talaba qo'shish">+</button></div>
                <div id="teamStudentSelectors" class="space-y-2"></div>
                <p class="text-xs text-slate-500">Bir qatorda bitta talaba. Takror tanlash bloklanadi.</p>
            </div>
            <div class="flex justify-end gap-2"><button type="button" class="px-4 py-2 border rounded" onclick="closeTeamModal()">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>

<div id="teamMemberModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-lg p-4">
        <h3 class="font-bold mb-3">Jamoaga a'zo qo'shish</h3>
        <form id="teamMemberForm" class="form-grid">
            <input type="hidden" name="team_id" id="teamMemberTeamId">
            <label class="form-field"><span class="form-label">Talaba</span><select id="teamMemberStudentSelect" name="student_id" class="form-input" required><option value="">Talaba tanlang</option><?php foreach($studentOptions as $s): ?><option value="<?= (int)$s['id']; ?>"><?= htmlspecialchars($s['fio']); ?></option><?php endforeach; ?></select></label>
            <div class="flex justify-end gap-2"><button type="button" class="px-4 py-2 border rounded" onclick="closeTeamMemberModal()">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Qo'shish</button></div>
        </form>
    </div>
</div>
