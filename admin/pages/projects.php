<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? ['status' => ''];
$studentOptions = $pageOptions['student_options'] ?? [];
$statusOptions = [
    'boshlanish' => 'Boshlanish',
    'qurish' => 'Qurish',
    'testlash' => 'Testlash',
    'tugallash' => 'Tugallash',
];
?>
<div class="p-4 space-y-4">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Loyihalar</h2>
            <p class="text-slate-500 text-sm">Loyihalar ro'yxati, statuslar va jamoalar tarkibini boshqarish.</p>
        </div>
        <button id="openProjectCreateModalBtn" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl shadow-sm hover:bg-emerald-700 transition-all flex items-center justify-center gap-2 font-semibold text-sm">
            <i class="fa-solid fa-plus"></i>
            Loyiha yaratish
        </button>
    </div>

    <form method="get" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-wrap gap-4 items-center">
        <input type="hidden" name="page" value="projects">
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select name="status" class="w-full md:w-60 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none transition-all">
                <option value="">Barcha holatlar</option>
                <?php foreach ($statusOptions as $key => $label): ?>
                    <option value="<?= $key; ?>" <?= (($filters['status'] ?? '') === $key) ? 'selected' : ''; ?>><?= $label; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="h-10 px-5 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition-all shadow-sm flex items-center gap-2 text-sm font-medium">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
            </button>
            <?php if (!empty($filters['status'])): ?>
                <a href="index.php?page=projects" class="h-10 w-10 flex items-center justify-center bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-all border border-rose-100" title="Tozalash">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php if (!$items): ?><p class="text-sm text-slate-500">Loyihalar mavjud emas.</p><?php endif; ?>
        <?php foreach ($items as $project): ?>
            <article class="rounded-xl bg-white p-4 shadow border border-slate-100">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="font-semibold text-slate-900"><?= htmlspecialchars($project['project_name']); ?></h3>
                    <div class="flex items-center gap-2">
                        <select class="form-input py-1 px-2 text-xs min-w-[120px] js-project-status-change" data-id="<?= (int) $project['id']; ?>">
                            <?php foreach ($statusOptions as $key => $label): ?>
                                <option value="<?= $key; ?>" <?= (($project['status'] ?? 'boshlanish') === $key) ? 'selected' : ''; ?>><?= $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="js-project-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" data-id="<?= (int) $project['id']; ?>" title="Loyihani o'chirish">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-1">A'zolar: <?= count($project['members'] ?? []); ?></p>
                <div class="mt-3 space-y-2 max-h-44 overflow-auto">
                    <?php if (empty($project['members'])): ?><p class="text-sm text-slate-500">Hali a'zo qo'shilmagan</p><?php endif; ?>
                    <?php foreach (($project['members'] ?? []) as $member): ?>
                        <div class="rounded-lg border border-slate-200 px-3 py-2 flex items-center justify-between"><span class="text-sm text-slate-700"><?= htmlspecialchars($member['fio']); ?></span><button type="button" class="js-project-member-remove text-xs px-2 py-1 rounded bg-slate-800 text-white" data-id="<?= (int)$member['id']; ?>">Chiqarish</button></div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="js-project-member-open mt-3 px-3 py-1.5 text-xs rounded bg-emerald-700 text-white" data-project-id="<?= (int)$project['id']; ?>">+ A'zo qo'shish</button>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if (($pagination['pages'] ?? 1) > 1): ?><div class="flex gap-2 flex-wrap"><?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?><a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page'=>'projects','p'=>$i,'status'=>$filters['status'] ?? ''])); ?>"><?= $i; ?></a><?php endfor; ?></div><?php endif; ?>
</div>

<div id="projectModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-xl p-4">
        <h3 class="font-bold mb-3">Loyiha yaratish</h3>
        <form id="projectForm" class="form-grid">
            <label class="form-field"><span class="form-label">Loyiha nomi</span><input name="project_name" class="form-input" placeholder="Masalan: CRM Platform" required></label>
            <label class="form-field">
                <span class="form-label">Loyiha holati</span>
                <select name="status" class="form-input" required>
                    <option value="boshlanish">Boshlanish</option>
                    <option value="qurish">Qurish</option>
                    <option value="testlash">Testlash</option>
                    <option value="tugallash">Tugallash</option>
                </select>
            </label>
            <div class="form-field">
                <div class="flex items-center justify-between gap-2"><span class="form-label">Talabalar</span><button type="button" id="addProjectStudentSelectBtn" class="h-8 w-8 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 flex items-center justify-center" title="Yana talaba qo'shish">+</button></div>
                <div id="projectStudentSelectors" class="space-y-2"></div>
                <p class="text-xs text-slate-500">Bir qatorda bitta talaba. Takror tanlash bloklanadi.</p>
            </div>
            <div class="flex justify-end gap-2"><button type="button" class="px-4 py-2 border rounded" onclick="closeProjectModal()">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>

<div id="projectMemberModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-lg p-4">
        <h3 class="font-bold mb-3">Loyihaga a'zo qo'shish</h3>
        <form id="projectMemberForm" class="form-grid">
            <input type="hidden" name="project_id" id="projectMemberProjectId">
            <label class="form-field"><span class="form-label">Talaba</span><select id="projectMemberStudentSelect" name="student_id" class="form-input" required><option value="">Talaba tanlang</option><?php foreach($studentOptions as $s): ?><option value="<?= (int)$s['id']; ?>"><?= htmlspecialchars($s['fio']); ?></option><?php endforeach; ?></select></label>
            <div class="flex justify-end gap-2"><button type="button" class="px-4 py-2 border rounded" onclick="closeProjectMemberModal()">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Qo'shish</button></div>
        </form>
    </div>
</div>

