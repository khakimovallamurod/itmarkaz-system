<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 justify-between items-center">
        <h2 class="font-semibold">Ish jadvali</h2>
        <div class="flex flex-wrap gap-2">
            <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-1">
                <button type="button" id="scheduleTypeAll" class="schedule-type-btn px-3 py-1.5 rounded-md text-sm bg-white shadow">Barchasi</button>
                <button type="button" id="scheduleTypeDaily" class="schedule-type-btn px-3 py-1.5 rounded-md text-sm">Kunlik</button>
                <button type="button" id="scheduleTypeWeekly" class="schedule-type-btn px-3 py-1.5 rounded-md text-sm">Haftalik</button>
            </div>
            <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-1">
                <button type="button" id="scheduleViewListBtn" class="px-3 py-1.5 rounded-md text-sm bg-white shadow">List</button>
                <button type="button" id="scheduleViewGridBtn" class="px-3 py-1.5 rounded-md text-sm">Jadval</button>
            </div>
            <button onclick="openScheduleModal()" class="px-4 py-2 bg-emerald-800 text-white rounded">+ Jadval qo'shish</button>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow overflow-auto">
        <div class="table-shell" id="scheduleTableWrap">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 18%;">
                    <col style="width: 44%;">
                    <col style="width: 18%;">
                    <col style="width: 20%;">
                </colgroup>
                <thead><tr><th>Type</th><th>Sarlavha</th><th>Sana</th><th>Actions</th></tr></thead>
                <tbody id="scheduleTableBody"></tbody>
            </table>
        </div>
        <div id="scheduleGrid" class="hidden grid md:grid-cols-2 xl:grid-cols-3 gap-3"></div>
    </div>
</div>

<div id="scheduleModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-lg p-4">
        <h3 class="font-bold mb-3">Jadval</h3>
        <form id="scheduleForm" class="form-grid">
            <input type="hidden" name="id">
            <label class="form-field">
                <span class="form-label">Turi</span>
                <select name="type" class="form-input" required><option value="weekly">Haftalik</option><option value="daily">Kunlik</option></select>
            </label>
            <label class="form-field">
                <span class="form-label">Sarlavha</span>
                <input name="title" placeholder="Sarlavha" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Sana</span>
                <input type="date" name="date" class="form-input" required>
            </label>
            <div class="flex justify-end gap-2"><button type="button" onclick="closeScheduleModal()" class="px-4 py-2 border rounded">Bekor</button><button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button></div>
        </form>
    </div>
</div>
