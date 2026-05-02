<?php
$items = $pageData['items'] ?? [];
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1];
$residentStudents = $pageOptions['resident_students'] ?? [];
$courses = $pageOptions['courses'] ?? [];
?>
<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl shadow p-4 flex justify-between items-center">
        <h3 class="font-semibold">Mentorlar</h3>
        <button onclick="openMentorModal()" class="bg-emerald-800 text-white px-4 py-2 rounded">+ Mentor qo'shish</button>
    </div>
    <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <div class="table-shell">
            <table class="admin-table w-full">
                <colgroup><col style="width: 60px;"><col style="width: 40%;"><col style="width: 35%;"><col style="width: 20%;"></colgroup>
                <thead><tr><th class="px-4 py-3">#</th><th class="px-4 py-3 text-left">FIO</th><th class="px-4 py-3 text-left">Kurs</th><th class="px-4 py-3 text-right">Amallar</th></tr></thead>
                <tbody>
                <?php if (!$items): ?><tr><td colspan="4" class="text-center text-slate-500 py-10">Ma'lumot topilmadi</td></tr><?php endif; ?>
                <?php 
                $totalCount = $pagination['total'] ?? 0;
                $offset = $pagination['offset'] ?? 0;
                foreach ($items as $index => $m): 
                    $rowNum = $totalCount - $offset - $index;
                    $rowJson = htmlspecialchars(json_encode($m, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); 
                ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-slate-400 font-mono text-xs"><?= $rowNum; ?></td>
                        <td class="px-4 py-3 font-medium text-slate-700"><?= htmlspecialchars($m['fio']); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($m['course_name']); ?></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="js-mentor-edit h-8 w-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors" data-item="<?= $rowJson; ?>">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button type="button" class="js-mentor-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" data-id="<?= (int)$m['id']; ?>">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?><div class="mt-3 flex gap-2 flex-wrap"><?php for($i=1;$i<=(int)$pagination['pages'];$i++): ?><a class="px-3 py-1 border rounded <?= $i === (int)$pagination['page'] ? 'bg-green-600 text-white' : ''; ?>" href="?<?= htmlspecialchars(http_build_query(['page'=>'mentors','p'=>$i])); ?>"><?= $i; ?></a><?php endfor; ?></div><?php endif; ?>
    </div>
</div>

<div id="mentorModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl p-6 w-full max-w-2xl">
        <h3 class="font-bold mb-3">Mentor</h3>
        <form id="mentorForm" class="form-grid md:grid-cols-2">
            <input type="hidden" name="id">
            <label class="form-field md:col-span-2"><span class="form-label">Rezident talaba</span><select name="student_id" id="mentorStudentSelect" class="form-input" required><option value="">Rezident tanlang</option><?php foreach ($residentStudents as $s): ?><option value="<?= (int)$s['id']; ?>"><?= htmlspecialchars($s['fio']); ?></option><?php endforeach; ?></select></label>
            <label class="form-field md:col-span-2"><span class="form-label">Kurs</span><select name="course_id" id="mentorCourseSelect" class="form-input" required><option value="">Kurs tanlang</option><?php foreach ($courses as $c): ?><option value="<?= (int)$c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select></label>
            <div class="md:col-span-2 flex justify-end gap-2"><button type="button" onclick="closeMentorModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
