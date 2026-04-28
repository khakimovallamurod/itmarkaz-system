<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow flex flex-wrap gap-2 items-center justify-between">
        <div>
            <h2 class="font-semibold">Upwork Jamoa</h2>
            <p class="text-xs text-slate-500">Jamoa yaratish, a'zolarni qo'shish va chiqarish</p>
        </div>
        <button id="openTeamCreateModalBtn" class="px-4 py-2 bg-emerald-800 text-white rounded">+ Jamoa yaratish</button>
    </div>

    <div id="teamsGrid" class="grid md:grid-cols-2 xl:grid-cols-3 gap-4"></div>
</div>

<div id="teamModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-xl p-4">
        <h3 class="font-bold mb-3">Jamoa yaratish</h3>
        <form id="teamForm" class="form-grid">
            <label class="form-field">
                <span class="form-label">Jamoa nomi</span>
                <input name="team_name" class="form-input" placeholder="Masalan: Team Falcon" required>
            </label>
            <div class="form-field">
                <div class="flex items-center justify-between gap-2">
                    <span class="form-label">Talabalar</span>
                    <button
                        type="button"
                        id="addTeamStudentSelectBtn"
                        class="h-8 w-8 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 flex items-center justify-center"
                        title="Yana talaba qo'shish">+</button>
                </div>
                <div id="teamStudentSelectors" class="space-y-2"></div>
                <p class="text-xs text-slate-500">Bir qatorda bitta talaba. Takror tanlash bloklanadi.</p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 border rounded" onclick="closeTeamModal()">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Saqlash</button>
            </div>
        </form>
    </div>
</div>

<div id="teamMemberModal" class="admin-modal hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-xl w-full max-w-lg p-4">
        <h3 class="font-bold mb-3">Jamoaga a'zo qo'shish</h3>
        <form id="teamMemberForm" class="form-grid">
            <input type="hidden" name="team_id" id="teamMemberTeamId">
            <label class="form-field">
                <span class="form-label">Talaba</span>
                <select id="teamMemberStudentSelect" name="student_id" class="form-input" required></select>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 border rounded" onclick="closeTeamMemberModal()">Bekor</button>
                <button class="px-4 py-2 bg-emerald-800 text-white rounded">Qo'shish</button>
            </div>
        </form>
    </div>
</div>
