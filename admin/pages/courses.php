<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl shadow p-4 flex justify-between items-center">
        <h3 class="font-semibold">Kurslar</h3>
        <button onclick="openCourseModal()" class="bg-emerald-800 text-white px-4 py-2 rounded">+ Kurs qo'shish</button>
    </div>
    <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 26%;">
                    <col style="width: 32%;">
                    <col style="width: 14%;">
                    <col style="width: 14%;">
                    <col style="width: 14%;">
                </colgroup>
                <thead><tr><th>Nomi</th><th>Kunlar</th><th>Vaqt</th><th>Davomiylik</th><th>Actions</th></tr></thead>
                <tbody id="coursesTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="courseModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl p-6 w-full max-w-xl">
        <h3 class="font-bold mb-3">Kurs</h3>
        <form id="courseForm" class="form-grid">
            <input type="hidden" name="id">
            <label class="form-field">
                <span class="form-label">Kurs nomi</span>
                <input name="name" class="form-input" placeholder="Masalan: Sun'iy intellekt" required>
            </label>
            <label class="form-field">
                <span class="form-label">Tavsif</span>
                <textarea name="description" class="form-input" placeholder="Qisqacha tavsif"></textarea>
            </label>
            <label class="form-field">
                <span class="form-label">Hafta kunlari</span>
            </label>
            <div id="courseDaysGrid" class="grid grid-cols-2 md:grid-cols-3 gap-2"></div>
            <label class="form-field">
                <span class="form-label">Vaqt</span>
                <input name="time" class="form-input" placeholder="Masalan: 18:00" required>
            </label>
            <label class="form-field">
                <span class="form-label">Davomiylik</span>
                <input name="duration" class="form-input" placeholder="Masalan: 2 oy" required>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeCourseModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button>
            </div>
        </form>
    </div>
</div>
