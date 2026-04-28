<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <h2 class="font-semibold">Kurs o'quvchilar</h2>
        <div class="flex flex-wrap gap-2">
            <input id="courseStudentSearch" class="border rounded px-3 py-2" placeholder="Qidirish...">
            <select id="courseStudentStatusFilter" class="border rounded px-3 py-2">
                <option value="">Barcha status</option>
                <option value="active">Faqat Kursda</option>
                <option value="completed">Faqat Tugatgan</option>
            </select>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow overflow-auto">
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 28%;">
                    <col style="width: 24%;">
                    <col style="width: 14%;">
                    <col style="width: 14%;">
                    <col style="width: 20%;">
                </colgroup>
                <thead><tr><th>FIO</th><th>Kurs</th><th>Xona</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="courseStudentsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="courseStudentModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <h3 class="font-bold mb-3">Kurs biriktirish</h3>
        <form id="courseStudentForm" class="form-grid">
            <input type="hidden" name="student_id" id="courseStudentId">
            <label class="form-field">
                <span class="form-label">Talaba</span>
                <input id="courseStudentName" class="form-input bg-slate-50" readonly>
            </label>
            <label class="form-field">
                <span class="form-label">Kurs</span>
                <select name="course_id" id="courseSelect" class="form-input" required></select>
            </label>
            <label class="form-field">
                <span class="form-label">Xona</span>
                <select name="room_id" id="courseRoomSelect" class="form-input"></select>
            </label>
            <div class="flex justify-end gap-2"><button type="button" onclick="closeCourseStudentModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
