<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl shadow p-4 flex justify-between items-center">
        <h3 class="font-semibold">Xonalar</h3>
        <button onclick="openRoomModal()" class="bg-emerald-800 text-white px-4 py-2 rounded">+ Xona qo'shish</button>
    </div>
    <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 30%;">
                    <col style="width: 20%;">
                    <col style="width: 26%;">
                    <col style="width: 24%;">
                </colgroup>
                <thead><tr><th>Xona</th><th>Sig'im</th><th>Kompyuter</th><th>Actions</th></tr></thead>
                <tbody id="roomsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="roomModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
        <h3 class="font-bold mb-3">Xona</h3>
        <form id="roomForm" class="form-grid">
            <input type="hidden" name="id">
            <label class="form-field">
                <span class="form-label">Xona raqami</span>
                <input name="room_number" class="form-input" placeholder="Masalan: 204" required>
            </label>
            <label class="form-field">
                <span class="form-label">Sig'im</span>
                <input type="number" name="capacity" class="form-input" placeholder="Masalan: 20" required>
            </label>
            <label class="form-field">
                <span class="form-label">Kompyuter soni</span>
                <input type="number" name="computers_count" class="form-input" placeholder="Masalan: 15" required>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeRoomModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button>
            </div>
        </form>
    </div>
</div>
