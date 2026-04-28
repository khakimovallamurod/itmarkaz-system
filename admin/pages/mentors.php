<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl shadow p-4 flex justify-between items-center">
        <h3 class="font-semibold">Mentorlar</h3>
        <button onclick="openMentorModal()" class="bg-emerald-800 text-white px-4 py-2 rounded">+ Mentor qo'shish</button>
    </div>
    <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 40%;">
                    <col style="width: 40%;">
                    <col style="width: 20%;">
                </colgroup>
                <thead><tr><th>FIO</th><th>Kurs</th><th>Actions</th></tr></thead>
                <tbody id="mentorsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="mentorModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl p-6 w-full max-w-2xl">
        <h3 class="font-bold mb-3">Mentor</h3>
        <form id="mentorForm" class="form-grid md:grid-cols-2">
            <input type="hidden" name="id">
            <label class="form-field md:col-span-2">
                <span class="form-label">Rezident talaba</span>
                <select name="student_id" id="mentorStudentSelect" class="form-input" required></select>
            </label>
            <label class="form-field md:col-span-2">
                <span class="form-label">Kurs</span>
                <select name="course_id" id="mentorCourseSelect" class="form-input" required></select>
            </label>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" onclick="closeMentorModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button>
            </div>
        </form>
    </div>
</div>
