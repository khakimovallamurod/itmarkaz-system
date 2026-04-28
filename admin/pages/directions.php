<div class="p-4">
    <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Yo'nalishlar ro'yxati</h3>
            <button onclick="openDirectionModal()" class="bg-emerald-800 text-white px-4 py-2 rounded">+ Qo'shish</button>
        </div>
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 72%;">
                    <col style="width: 28%;">
                </colgroup>
                <thead><tr><th>Nomi</th><th>Actions</th></tr></thead>
                <tbody id="directionsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="directionModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
        <h3 class="font-bold mb-3">Yo'nalish</h3>
        <form id="directionForm" class="form-grid">
            <input type="hidden" name="id" id="directionId">
            <label class="form-field">
                <span class="form-label">Yo'nalish nomi</span>
                <input name="name" id="directionName" class="form-input" placeholder="Masalan: Frontend" required>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDirectionModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button>
            </div>
        </form>
    </div>
</div>
