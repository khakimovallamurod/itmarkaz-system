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
            <table class="admin-table">
                <colgroup><col style="width: 40%;"><col style="width: 40%;"><col style="width: 20%;"></colgroup>
                <thead><tr><th>FIO</th><th>Kurs</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$items): ?><tr><td colspan="3" class="text-center text-slate-500">Ma'lumot topilmadi</td></tr><?php endif; ?>
                <?php foreach ($items as $m): ?>
                    <?php $rowJson = htmlspecialchars(json_encode($m, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
                    <tr><td><?= htmlspecialchars($m['fio']); ?></td><td><?= htmlspecialchars($m['course_name']); ?></td><td><div class="table-actions"><button type="button" class="js-mentor-edit px-2 py-1 text-xs border rounded" data-item="<?= $rowJson; ?>">Edit</button><button type="button" class="js-mentor-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="<?= (int)$m['id']; ?>">Delete</button></div></td></tr>
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
