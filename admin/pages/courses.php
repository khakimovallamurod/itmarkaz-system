<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$weekDays = $pageOptions['week_days'] ?? [];
?>
<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl shadow p-4 flex justify-between items-center">
        <h3 class="font-semibold">Kurslar</h3>
        <button onclick="openCourseModal()" class="bg-emerald-800 text-white px-4 py-2 rounded">+ Kurs qo'shish</button>
    </div>
    <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <div class="table-shell">
            <table class="admin-table w-full">
                <colgroup><col style="width: 60px;"><col style="width: 26%;"><col style="width: 32%;"><col style="width: 14%;"><col style="width: 14%;"><col style="width: 14%;"></colgroup>
                <thead><tr><th class="px-4 py-3">#</th><th class="px-4 py-3 text-left">Nomi</th><th class="px-4 py-3 text-left">Kunlar</th><th class="px-4 py-3 text-left">Vaqt</th><th class="px-4 py-3 text-left text-xs">Davomiylik</th><th class="px-4 py-3 text-right">Amallar</th></tr></thead>
                <tbody>
                <?php if (!$items): ?><tr><td colspan="6" class="text-center text-slate-500 py-10">Ma'lumot topilmadi</td></tr><?php endif; ?>
                <?php 
                $totalCount = $pagination['total'] ?? 0;
                $offset = $pagination['offset'] ?? 0;
                foreach ($items as $index => $c): 
                    $rowNum = $totalCount - $offset - $index;
                    $days = json_decode((string) ($c['days'] ?? '[]'), true);
                    if (!is_array($days)) $days = [];
                    $c['days'] = $days;
                    $rowJson = htmlspecialchars(json_encode($c, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-slate-400 font-mono text-xs"><?= $rowNum; ?></td>
                        <td class="px-4 py-3 font-medium text-slate-700"><?= htmlspecialchars($c['name']); ?></td>
                        <td class="px-4 py-3 text-slate-600">
                            <div class="flex flex-wrap gap-1">
                                <?php foreach ($days as $day): ?>
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-medium"><?= htmlspecialchars($day); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($c['time']); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($c['duration']); ?></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="js-course-edit h-8 w-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors" data-item="<?= $rowJson; ?>">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button type="button" class="js-course-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" data-id="<?= (int)$c['id']; ?>">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?><div class="mt-3 flex gap-2 flex-wrap"><?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?><a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page'=>'courses','p'=>$i])); ?>"><?= $i; ?></a><?php endfor; ?></div><?php endif; ?>
    </div>
</div>

<div id="courseModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl p-6 w-full max-w-xl">
        <h3 class="font-bold mb-3">Kurs</h3>
        <form id="courseForm" class="form-grid">
            <input type="hidden" name="id">
            <label class="form-field"><span class="form-label">Kurs nomi</span><input name="name" class="form-input" placeholder="Masalan: Sun'iy intellekt" required></label>
            <label class="form-field"><span class="form-label">Tavsif</span><textarea name="description" class="form-input" placeholder="Qisqacha tavsif"></textarea></label>
            <label class="form-field"><span class="form-label">Hafta kunlari</span></label>
            <div id="courseDaysGrid" class="grid grid-cols-2 md:grid-cols-3 gap-2">
                <?php foreach ($weekDays as $day): ?>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2"><input type="checkbox" name="days[]" value="<?= htmlspecialchars($day['code']); ?>" class="h-4 w-4 accent-emerald-700"><span class="text-sm"><?= htmlspecialchars($day['name']); ?></span></label>
                <?php endforeach; ?>
            </div>
            <label class="form-field"><span class="form-label">Vaqt</span><input name="time" class="form-input" placeholder="Masalan: 18:00" required></label>
            <label class="form-field"><span class="form-label">Davomiylik</span><input name="duration" class="form-input" placeholder="Masalan: 2 oy" required></label>
            <div class="flex justify-end gap-2"><button type="button" onclick="closeCourseModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
