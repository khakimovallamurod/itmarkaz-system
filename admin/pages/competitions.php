<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <div>
            <h2 class="font-semibold">Tanlovlar</h2>
            <p class="text-xs text-slate-500">Card ko'rinish, detail sahifa, xabar yuborish va natija boshqaruvi</p>
        </div>
        <button onclick="openCompetitionModal()" class="px-4 py-2 bg-emerald-800 text-white rounded">+ Tanlov qo'shish</button>
    </div>

    <div id="competitionsGrid" class="grid md:grid-cols-2 xl:grid-cols-3 gap-4"></div>
</div>

<div id="competitionModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-xl p-4">
        <h3 class="font-bold mb-3">Tanlov</h3>
        <form id="competitionForm" class="form-grid">
            <input type="hidden" name="id">
            <label class="form-field">
                <span class="form-label">Nomi</span>
                <input name="name" placeholder="Nomi" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Tavsif</span>
                <textarea name="description" placeholder="Qisqacha tavsif" class="form-input"></textarea>
            </label>
            <label class="form-field">
                <span class="form-label">Manzil</span>
                <input name="location" placeholder="Masalan: IT Park, 3-zal" class="form-input">
            </label>
            <label class="form-field">
                <span class="form-label">Ro'yxat deadline</span>
                <input type="date" name="registration_deadline" class="form-input" required>
            </label>
            <label class="form-field">
                <span class="form-label">Tanlov sanasi</span>
                <input type="date" name="competition_date" class="form-input" required>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeCompetitionModal()" class="px-4 py-2 border rounded">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button>
            </div>
        </form>
    </div>
</div>
