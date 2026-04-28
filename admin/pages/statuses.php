<div class="p-4">
    <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Statuslar ro'yxati</h3>
            <button onclick="openStatusModal()" class="bg-emerald-800 text-white px-4 py-2 rounded">+ Qo'shish</button>
        </div>
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 72%;">
                    <col style="width: 28%;">
                </colgroup>
                <thead><tr><th>Nomi</th><th>Actions</th></tr></thead>
                <tbody id="statusesTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="statusModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
        <h3 class="font-bold mb-3">Status</h3>
        <form id="statusForm" class="form-grid">
            <input type="hidden" name="id" id="statusId">
            <label class="form-field">
                <span class="form-label">Status nomi</span>
                <input name="name" id="statusName" class="form-input" placeholder="Masalan: Rezident" required>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeStatusModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button>
            </div>
        </form>
    </div>
</div>
