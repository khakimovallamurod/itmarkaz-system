<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? ['level' => ''];
$studentOptions = $pageOptions['student_options'] ?? [];
$teamLevels = [
    'junior' => 'Junior',
    'middle' => 'Middle',
    'senior' => 'Senior',
];
?>
<div class="p-4 space-y-4">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Upwork Jamoalari</h2>
            <p class="text-slate-500 text-sm">Jamoalarni boshqarish, a'zolar tarkibi va darajalarini sozlash.</p>
        </div>
        <button id="openTeamCreateModalBtn" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl shadow-sm hover:bg-emerald-700 transition-all flex items-center justify-center gap-2 font-semibold text-sm">
            <i class="fa-solid fa-plus"></i>
            Jamoa yaratish
        </button>
    </div>

    <form method="get" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-wrap gap-4 items-center">
        <input type="hidden" name="page" value="teams">
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select name="level" class="w-full md:w-60 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none transition-all">
                <option value="">Barcha darajalar</option>
                <?php foreach ($teamLevels as $levelKey => $levelLabel): ?>
                    <option value="<?= $levelKey; ?>" <?= (($filters['level'] ?? '') === $levelKey) ? 'selected' : ''; ?>><?= $levelLabel; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="h-10 px-5 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition-all shadow-sm flex items-center gap-2 text-sm font-medium">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
            </button>
            <?php if (!empty($filters['level'])): ?>
                <a href="index.php?page=teams" class="h-10 w-10 flex items-center justify-center bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-all border border-rose-100" title="Tozalash">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php if (!$items): ?><p class="text-sm text-slate-500">Jamoalar mavjud emas.</p><?php endif; ?>
        <?php foreach ($items as $team): ?>
            <article class="rounded-xl bg-white p-4 shadow border border-slate-100">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="font-semibold text-slate-900"><?= htmlspecialchars($team['team_name']); ?></h3>
                    <div class="flex items-center gap-2">
                        <select class="form-input py-1 px-2 text-xs min-w-[110px] js-team-level-change" data-id="<?= (int) $team['id']; ?>">
                            <?php foreach ($teamLevels as $levelKey => $levelLabel): ?>
                                <option value="<?= $levelKey; ?>" <?= (($team['level'] ?? 'middle') === $levelKey) ? 'selected' : ''; ?>><?= $levelLabel; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="js-team-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" data-id="<?= (int)$team['id']; ?>" title="Jamoani o'chirish">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>
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
    <?php if (($pagination['pages'] ?? 1) > 1): ?><div class="flex gap-2 flex-wrap"><?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?><a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page'=>'teams','p'=>$i,'level'=>$filters['level'] ?? ''])); ?>"><?= $i; ?></a><?php endfor; ?></div><?php endif; ?>
</div>

<div id="teamModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-xl p-4">
        <h3 class="font-bold mb-3">Jamoa yaratish</h3>
        <form id="teamForm" class="form-grid">
            <label class="form-field"><span class="form-label">Jamoa nomi</span><input name="team_name" class="form-input" placeholder="Masalan: Team Falcon" required></label>
            <label class="form-field"><span class="form-label">Jamoa darajasi</span><select name="level" class="form-input" required><option value="junior">Junior</option><option value="middle" selected>Middle</option><option value="senior">Senior</option></select></label>
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
