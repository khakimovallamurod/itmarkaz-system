<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <div class="flex gap-2 flex-wrap">
            <input id="studentSearch" class="border rounded px-3 py-2" placeholder="Qidirish...">
            <select id="studentDirectionFilter" class="border rounded px-3 py-2">
                <option value="">Barcha yo'nalishlar</option>
            </select>
        </div>
        <button onclick="openStudentModal()" class="px-4 py-2 bg-emerald-800 text-white rounded">+ Talaba qo'shish</button>
    </div>

    <div class="bg-white rounded-xl p-4 shadow overflow-auto">
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 16%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 14%;">
                    <col style="width: 18%;">
                    <col style="width: 12%;">
                </colgroup>
                <thead><tr><th>FIO</th><th>Yo'nalish</th><th>Guruh</th><th>Kurs</th><th>Telefon</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="studentsTableBody"></tbody>
            </table>
        </div>
        <div id="studentsPagination" class="mt-3 flex gap-2"></div>
    </div>
</div>

<div id="studentModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-2xl p-5">
        <h3 id="studentModalTitle" class="font-bold mb-3">Talaba qo'shish</h3>
        <form id="studentForm" class="form-grid md:grid-cols-2">
            <input type="hidden" name="id" id="studentId">
            <label class="form-field">
                <span class="form-label">FIO</span>
                <input name="fio" placeholder="Masalan: Ali Valiyev" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Yo'nalish</span>
                <select name="yonalish_id" id="studentDirectionSelect" class="form-input" required></select>
            </label>
            <label class="form-field">
                <span class="form-label">Guruh</span>
                <input name="guruh" placeholder="Masalan: FE-12" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Kirgan yili</span>
                <input type="number" name="kirgan_yili" id="studentEntryYear" placeholder="Masalan: 2024" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Kurs (avtomatik)</span>
                <input type="text" id="studentAutoCourse" class="form-input bg-slate-50" readonly>
            </label>
            <label class="form-field">
                <span class="form-label">Telefon</span>
                <input name="telefon" data-phone-input placeholder="+998 90 123 45 67" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Telegram Chat ID</span>
                <input name="telegram_chat_id" placeholder="Masalan: 12345678" class="form-input">
            </label>
            <div class="md:col-span-2">
                <label class="form-label">Statuslar</label>
                <div id="studentStatusCheckboxes" class="mt-2 grid sm:grid-cols-2 gap-2 rounded-lg border border-slate-200 p-3 bg-slate-50"></div>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" onclick="closeStudentModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button>
            </div>
        </form>
    </div>
</div>
