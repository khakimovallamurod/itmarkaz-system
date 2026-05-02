<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$filters = $pageData['filters'] ?? ['date_from' => '', 'date_to' => '', 'period' => ''];
$report = $pageData['report'] ?? [
    'competitions_count' => 0,
    'participants_count' => 0,
    'winners_count' => 0,
    'period_names' => ['past' => [], 'upcoming_15' => [], 'upcoming_after_15' => []],
];
$periodNames = $report['period_names'] ?? ['past' => [], 'upcoming_15' => [], 'upcoming_after_15' => []];
?>
<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <div><h2 class="font-semibold">Tanlovlar</h2><p class="text-xs text-slate-500">Card ko'rinish, detail sahifa, xabar yuborish va natija boshqaruvi</p></div>
        <div class="flex items-center gap-2">
            <button type="button" id="openCompetitionReportBtn" class="px-4 py-2 bg-slate-800 text-white rounded">Hisobot</button>
            <button onclick="openCompetitionModal()" class="px-4 py-2 bg-emerald-800 text-white rounded">+ Tanlov qo'shish</button>
        </div>
    </div>

    <form method="get" class="bg-white rounded-xl p-4 shadow">
        <input type="hidden" name="page" value="competitions">
        <div class="grid md:grid-cols-5 gap-3">
            <label class="form-field">
                <span class="form-label">Davr filtri</span>
                <select name="period" class="form-input">
                    <option value="" <?= ($filters['period'] ?? '') === '' ? 'selected' : ''; ?>>Sana oralig'i bo'yicha</option>
                    <option value="past" <?= ($filters['period'] ?? '') === 'past' ? 'selected' : ''; ?>>O'tgan tanlovlar</option>
                    <option value="upcoming_15" <?= ($filters['period'] ?? '') === 'upcoming_15' ? 'selected' : ''; ?>>15 kun ichidagi yaqin tanlovlar</option>
                    <option value="upcoming_after_15" <?= ($filters['period'] ?? '') === 'upcoming_after_15' ? 'selected' : ''; ?>>15 kundan keyingi tanlovlar</option>
                </select>
            </label>
            <label class="form-field">
                <span class="form-label">Boshlanish sana</span>
                <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? ''); ?>" class="form-input">
            </label>
            <label class="form-field">
                <span class="form-label">Tugash sana</span>
                <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? ''); ?>" class="form-input">
            </label>
            <div class="form-field md:col-span-2">
                <span class="form-label opacity-0 select-none">Action</span>
                <div class="flex gap-2">
                    <button class="px-4 py-2 border rounded">Filter</button>
                    <a href="?page=competitions" class="px-4 py-2 border rounded">Tozalash</a>
                </div>
            </div>
        </div>
    </form>

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
                <div class="flex gap-2 mt-4" data-competition-actions>
                    <button type="button" class="js-competition-edit h-8 w-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors" data-item="<?= $data; ?>" title="Tahrirlash">
                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                    </button>
                    <button type="button" class="js-competition-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" data-id="<?= (int) $item['id']; ?>" title="O'chirish">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (($pagination['pages'] ?? 1) > 1): ?>
        <div class="flex gap-2 flex-wrap">
            <?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?>
                <a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query([
                    'page' => 'competitions',
                    'p' => $i,
                    'period' => $filters['period'] ?? '',
                    'date_from' => $filters['date_from'] ?? '',
                    'date_to' => $filters['date_to'] ?? '',
                ])); ?>"><?= $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<div id="competitionReportModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl w-full max-w-4xl p-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="font-bold text-lg">Tanlovlar hisoboti</h3>
            <button type="button" onclick="closeCompetitionReportModal()" class="px-3 py-1.5 border rounded">Yopish</button>
        </div>
        <div class="mb-4">
            <label class="form-label mb-1 block">Sana oralig'i (kassa uslubida)</label>
            <input
                type="text"
                id="competitionReportDateRange"
                class="form-input"
                placeholder="Sanani tanlang (Masalan: 2026-05-01 to 2026-05-20)"
                data-default-from="<?= htmlspecialchars($filters['date_from'] ?? ''); ?>"
                data-default-to="<?= htmlspecialchars($filters['date_to'] ?? ''); ?>">
        </div>
        <div class="grid md:grid-cols-3 gap-3 mb-4">
            <article class="rounded-xl border border-slate-200 p-4 bg-slate-50">
                <p class="text-xs text-slate-500">Tanlovlar soni</p>
                <p id="reportCompetitionsCount" class="text-3xl font-semibold text-slate-900 mt-1"><?= (int) ($report['competitions_count'] ?? 0); ?></p>
            </article>
            <article class="rounded-xl border border-slate-200 p-4 bg-slate-50">
                <p class="text-xs text-slate-500">Ishtirokchilar soni</p>
                <p id="reportParticipantsCount" class="text-3xl font-semibold text-slate-900 mt-1"><?= (int) ($report['participants_count'] ?? 0); ?></p>
            </article>
            <article class="rounded-xl border border-slate-200 p-4 bg-slate-50">
                <p class="text-xs text-slate-500">Sovrindorlar soni</p>
                <p id="reportWinnersCount" class="text-3xl font-semibold text-slate-900 mt-1"><?= (int) ($report['winners_count'] ?? 0); ?></p>
            </article>
        </div>
        <div class="grid lg:grid-cols-3 gap-4">
            <section class="rounded-xl border border-slate-200 p-3">
                <h4 class="font-semibold text-sm mb-2">O'tgan tanlovlar</h4>
                <div id="reportPastNames" class="space-y-1 text-sm text-slate-700 max-h-56 overflow-auto">
                    <?php foreach (($periodNames['past'] ?? []) as $name): ?>
                        <p>• <?= htmlspecialchars($name); ?></p>
                    <?php endforeach; ?>
                    <?php if (empty($periodNames['past'])): ?><p class="text-slate-500">Mavjud emas.</p><?php endif; ?>
                </div>
            </section>
            <section class="rounded-xl border border-slate-200 p-3">
                <h4 class="font-semibold text-sm mb-2">15 kun ichidagi yaqin tanlovlar</h4>
                <div id="reportUpcoming15Names" class="space-y-1 text-sm text-slate-700 max-h-56 overflow-auto">
                    <?php foreach (($periodNames['upcoming_15'] ?? []) as $name): ?>
                        <p>• <?= htmlspecialchars($name); ?></p>
                    <?php endforeach; ?>
                    <?php if (empty($periodNames['upcoming_15'])): ?><p class="text-slate-500">Mavjud emas.</p><?php endif; ?>
                </div>
            </section>
            <section class="rounded-xl border border-slate-200 p-3">
                <h4 class="font-semibold text-sm mb-2">15 kundan keyingi tanlovlar</h4>
                <div id="reportUpcomingAfter15Names" class="space-y-1 text-sm text-slate-700 max-h-56 overflow-auto">
                    <?php foreach (($periodNames['upcoming_after_15'] ?? []) as $name): ?>
                        <p>• <?= htmlspecialchars($name); ?></p>
                    <?php endforeach; ?>
                    <?php if (empty($periodNames['upcoming_after_15'])): ?><p class="text-slate-500">Mavjud emas.</p><?php endif; ?>
                </div>
            </section>
        </div>
    </div>
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
