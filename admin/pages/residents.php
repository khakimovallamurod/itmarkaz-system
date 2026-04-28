<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <h2 class="font-semibold">Rezidentlar</h2>
        <div class="flex flex-wrap gap-2">
            <input id="residentSearch" class="border rounded px-3 py-2" placeholder="Qidirish...">
            <select id="residentStatusFilter" class="border rounded px-3 py-2">
                <option value="">Barchasi</option>
                <option value="assigned">Xona berilgan</option>
                <option value="unassigned">Xona berilmagan</option>
            </select>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow overflow-auto">
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 34%;">
                    <col style="width: 20%;">
                    <col style="width: 24%;">
                    <col style="width: 22%;">
                </colgroup>
                <thead><tr><th>FIO</th><th>Xona</th><th>Kompyuter</th><th>Actions</th></tr></thead>
                <tbody id="residentsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="residentModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <h3 class="font-bold mb-3">Rezidentga xona berish</h3>
        <form id="residentForm" class="form-grid">
            <input type="hidden" name="student_id" id="residentStudentId">
            <label class="form-field">
                <span class="form-label">Talaba</span>
                <input id="residentStudentName" class="form-input bg-slate-50" readonly>
            </label>
            <label class="form-field">
                <span class="form-label">Xona</span>
                <select name="room_id" id="residentRoomSelect" class="form-input"></select>
            </label>
            <label class="form-field">
                <span class="form-label">Kompyuter raqami</span>
                <input name="computer_number" placeholder="Masalan: PC-07" class="form-input">
            </label>
            <div class="flex justify-end gap-2"><button type="button" onclick="closeResidentModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
