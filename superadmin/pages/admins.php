<?php
$adminsList = $pageData['admins'] ?? [];
$residentStudents = $pageData['resident_students'] ?? [];
$stats = $pageData['stats'] ?? ['total' => 0, 'active' => 0, 'blocked' => 0];
$filters = $pageData['filters'] ?? ['search' => '', 'status' => ''];
?>

<div class="space-y-6">
    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jami Adminlar</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= (int) $stats['total']; ?></h3>
            </div>
            <div class="h-11 w-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-users-gear"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Faol Adminlar</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1"><?= (int) $stats['active']; ?></h3>
            </div>
            <div class="h-11 w-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Bloklanganlar</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1"><?= (int) $stats['blocked']; ?></h3>
            </div>
            <div class="h-11 w-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-slash"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Action Bar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col md:flex-row gap-4 items-center justify-between">
        <form method="get" class="flex flex-col sm:flex-row gap-3 items-center w-full md:w-auto">
            <input type="hidden" name="page" value="admins">
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input name="search" value="<?= htmlspecialchars($filters['search'] ?? ''); ?>" 
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none transition-all" 
                       placeholder="Ism, login yoki telefon...">
            </div>

            <select name="status" onchange="this.form.submit()" class="w-full sm:w-44 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none transition-all">
                <option value="">Barcha statuslar</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Faol</option>
                <option value="blocked" <?= ($filters['status'] ?? '') === 'blocked' ? 'selected' : ''; ?>>Bloklangan</option>
            </select>

            <button type="submit" class="h-9 px-4 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition-all text-sm font-medium flex items-center gap-2">
                <i class="fa-solid fa-filter text-xs"></i> Filter
            </button>

            <?php if (!empty($filters['search']) || !empty($filters['status'])): ?>
                <a href="index.php?page=admins" class="h-9 w-9 flex items-center justify-center bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-all border border-rose-100" title="Tozalash">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </a>
            <?php endif; ?>
        </form>

        <button type="button" onclick="openCreateAdminModal()" class="w-full md:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-sm hover:shadow-emerald-200 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Yangi admin yaratish</span>
        </button>
    </div>

    <!-- Admins Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="table-shell">
            <table class="admin-table">
                <colgroup>
                    <col style="width: 5%;">
                    <col style="width: 25%;">
                    <col style="width: 15%;">
                    <col style="width: 20%;">
                    <col style="width: 12%;">
                    <col style="width: 10%;">
                    <col style="width: 13%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Administrator</th>
                        <th>Telefon</th>
                        <th>Bog'langan Rezident</th>
                        <th>Roli</th>
                        <th>Holati</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($adminsList)): ?>
                        <tr><td colspan="7" class="text-center text-slate-400 py-10">Adminlar topilmadi</td></tr>
                    <?php endif; ?>

                    <?php foreach ($adminsList as $idx => $adm): 
                        $isSuper = ($adm['role'] ?? '') === 'superadmin';
                        $isBlocked = ($adm['status'] ?? '') === 'blocked';
                        $displayName = trim(($adm['first_name'] ?? '') . ' ' . ($adm['last_name'] ?? ''));
                        if ($displayName === '') $displayName = $adm['username'];
                        $init = strtoupper(substr($displayName, 0, 1));
                    ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-slate-400 font-mono text-xs"><?= $idx + 1; ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl <?= $isSuper ? 'bg-purple-100 text-purple-700 font-bold' : 'bg-emerald-100 text-emerald-700 font-bold'; ?> flex items-center justify-center text-sm">
                                        <?= $init; ?>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800 text-sm leading-tight truncate"><?= htmlspecialchars($displayName); ?></p>
                                        <p class="text-xs text-slate-400 font-mono">@<?= htmlspecialchars($adm['username']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs font-mono">
                                <?= htmlspecialchars($adm['phone'] ?: 'Kiritilmagan'); ?>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <?php if (!empty($adm['student_fio'])): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-medium">
                                        <i class="fa-solid fa-graduation-cap text-[10px]"></i>
                                        <span class="truncate max-w-[150px]"><?= htmlspecialchars($adm['student_fio']); ?></span>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($isSuper): ?>
                                    <span class="px-2.5 py-1 rounded-full bg-purple-100 text-purple-700 text-[11px] font-extrabold uppercase tracking-wider">Super Admin</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[11px] font-semibold">Admin</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($isBlocked): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-semibold">
                                        <i class="fa-solid fa-circle-xmark text-[10px]"></i> Bloklangan
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Faol
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Block / Unblock Toggle -->
                                    <?php if (!$isSuper): ?>
                                        <button type="button" onclick="toggleAdminStatus(<?= (int) $adm['id']; ?>, '<?= $isBlocked ? 'active' : 'blocked'; ?>')" 
                                                class="h-8 w-8 rounded-lg border <?= $isBlocked ? 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' : 'border-rose-200 text-rose-600 hover:bg-rose-50'; ?> flex items-center justify-center transition-all" 
                                                title="<?= $isBlocked ? 'Blokdan chiqarish' : 'Bloklash'; ?>">
                                            <i class="fa-solid <?= $isBlocked ? 'fa-lock-open' : 'fa-lock'; ?> text-xs"></i>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Reset Password -->
                                    <button type="button" onclick="openResetPasswordModal(<?= (int) $adm['id']; ?>, '<?= htmlspecialchars($adm['username'], ENT_QUOTES); ?>')" 
                                            class="h-8 w-8 rounded-lg border border-amber-200 text-amber-600 hover:bg-amber-50 flex items-center justify-center transition-all" 
                                            title="Yangi parol berish">
                                        <i class="fa-solid fa-key text-xs"></i>
                                    </button>

                                    <!-- Delete Admin -->
                                    <?php if (!$isSuper && (int)$adm['id'] !== (int)$_SESSION['admin_id']): ?>
                                        <button type="button" onclick="deleteAdmin(<?= (int) $adm['id']; ?>, '<?= htmlspecialchars($adm['username'], ENT_QUOTES); ?>')" 
                                                class="h-8 w-8 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 flex items-center justify-center transition-all" 
                                                title="O'chirish">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Admin -->
<div id="createAdminModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-emerald-600"></i>
                Yangi admin yaratish
            </h3>
            <button type="button" onclick="closeCreateAdminModal()" class="h-8 w-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="createAdminForm" class="space-y-4">
            <!-- Resident Student Select -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-700">
                        Rezident talabani tanlang <span class="text-xs text-emerald-600 font-normal">(avtomatik to'ldirish uchun)</span>
                    </label>
                    <span class="text-[11px] text-slate-400 font-normal flex items-center gap-1">
                        <i class="fa-solid fa-magnifying-glass text-[10px]"></i> Qidirish (Select2)
                    </span>
                </div>
                <select id="adminResidentSelect" name="student_id" class="w-full">
                    <option value="">-- Rezident talaba tanlanmadi (qo'lda kiritish) --</option>
                    <?php foreach ($residentStudents as $res): ?>
                        <option value="<?= (int) $res['id']; ?>" 
                                data-fio="<?= htmlspecialchars($res['fio'], ENT_QUOTES); ?>" 
                                data-phone="<?= htmlspecialchars($res['telefon'], ENT_QUOTES); ?>"
                                data-group="<?= htmlspecialchars($res['guruh'], ENT_QUOTES); ?>"
                                data-direction="<?= htmlspecialchars($res['direction'] ?? '', ENT_QUOTES); ?>">
                            <?= htmlspecialchars($res['fio']); ?> (<?= htmlspecialchars($res['guruh']); ?> - <?= htmlspecialchars($res['direction'] ?? ''); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Rezident tanlansa, ism, familiya, telefon va login uning ma'lumotlari asosida to'ldiriladi.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ism <span class="text-rose-500">*</span></label>
                    <input type="text" id="adminNewFirstName" name="first_name" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none" required placeholder="Ali">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Familiya</label>
                    <input type="text" id="adminNewLastName" name="last_name" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none" placeholder="Valiyev">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Telefon raqami <span class="text-rose-500">*</span></label>
                <input type="text" id="adminNewPhone" name="phone" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none" required placeholder="+998 90 123 45 67">
            </div>

            <!-- Username Generator -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-semibold text-slate-700">Login (foydalanuvchi nomi) <span class="text-rose-500">*</span></label>
                    <button type="button" onclick="generateUsernameFromInputs()" class="text-[11px] text-emerald-600 hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> FIOdan generatsiya
                    </button>
                </div>
                <div class="relative">
                    <i class="fa-solid fa-at absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="adminNewUsername" name="username" class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none" required placeholder="login_nomi">
                </div>
            </div>

            <!-- Password with Regenerate button -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-semibold text-slate-700">Parol <span class="text-rose-500">*</span></label>
                    <button type="button" onclick="generateRandomPassword()" class="text-[11px] text-emerald-600 hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Qayta generate qilish
                    </button>
                </div>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-key absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="adminNewPassword" name="password" class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:bg-white focus:border-emerald-500 outline-none" required>
                    </div>
                    <button type="button" onclick="generateRandomPassword()" class="px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium flex items-center gap-1 transition-all" title="Yangi tasodifiy parol">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Ushbu parolni adminga berish uchun nusxalab oling.</p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Roli</label>
                    <select name="role" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none">
                        <option value="admin">Oddiy Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Boshlang'ich holat</label>
                    <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none">
                        <option value="active">Faol</option>
                        <option value="blocked">Bloklangan</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateAdminModal()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl">Bekor qilish</button>
                <button type="submit" id="submitCreateAdminBtn" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-check"></i> Saqlash
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reset Password -->
<div id="resetPasswordModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-key text-amber-500"></i>
                Yangi parol berish: <span id="resetUsernameSpan" class="text-emerald-700 font-mono"></span>
            </h3>
            <button type="button" onclick="closeResetPasswordModal()" class="h-8 w-8 rounded-lg hover:bg-slate-100 text-slate-400 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="resetPasswordForm" class="space-y-4">
            <input type="hidden" id="resetAdminId" name="admin_id">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Yangi parol</label>
                <div class="flex gap-2">
                    <input type="text" id="resetNewPasswordInput" name="new_password" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 outline-none" required>
                    <button type="button" onclick="generateResetPassword()" class="px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs flex items-center gap-1 font-medium">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl">Bekor qilish</button>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-sm rounded-xl flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i> Yangilash
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function transliterate(str) {
    const map = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'j', 'з': 'z',
        'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r',
        'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'x', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh',
        'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya', 'ў': 'o', 'қ': 'q', 'ғ': 'g', 'ҳ': 'h',
        'А': 'a', 'Б': 'b', 'В': 'v', 'Г': 'g', 'Д': 'd', 'Е': 'e', 'Ё': 'yo', 'Ж': 'j', 'З': 'z',
        'И': 'i', 'Й': 'y', 'К': 'k', 'Л': 'l', 'М': 'm', 'Н': 'n', 'О': 'o', 'П': 'p', 'Р': 'r',
        'С': 's', 'Т': 't', 'У': 'u', 'Ф': 'f', 'Х': 'x', 'Ц': 'ts', 'Ч': 'ch', 'Ш': 'sh', 'Щ': 'sh',
        'Ъ': '', 'Ы': 'y', 'Ь': '', 'Э': 'e', 'Ю': 'yu', 'Я': 'ya', 'Ў': 'o', 'Қ': 'q', 'Ғ': 'g', 'Ҳ': 'h',
        "'": '', "’": '', "`": ''
    };
    return str.split('').map(c => map[c] !== undefined ? map[c] : c).join('').toLowerCase().replace(/[^a-z0-9_]/g, '');
}

function generateRandomPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
    let pwd = 'Admin';
    for (let i = 0; i < 4; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('adminNewPassword').value = pwd;
}

function generateResetPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
    let pwd = 'Admin';
    for (let i = 0; i < 4; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('resetNewPasswordInput').value = pwd;
}

function generateUsernameFromInputs() {
    const first = document.getElementById('adminNewFirstName').value.trim();
    const last = document.getElementById('adminNewLastName').value.trim();
    if (!first && !last) return;
    
    let base = '';
    if (first && last) {
        base = transliterate(first) + '_' + transliterate(last);
    } else if (first) {
        base = transliterate(first);
    } else {
        base = transliterate(last);
    }
    document.getElementById('adminNewUsername').value = base;
}

function fillResidentData(selectEl) {
    if (!selectEl) return;
    const opt = selectEl.options[selectEl.selectedIndex];
    if (!opt || !opt.value) return;

    const fio = opt.getAttribute('data-fio') || '';
    const phone = opt.getAttribute('data-phone') || '';
    
    if (phone) {
        document.getElementById('adminNewPhone').value = phone;
    }

    if (fio) {
        const parts = fio.trim().split(/\s+/);
        // Typically: Last First Middle or First Last
        const lastName = parts[0] || '';
        const firstName = parts[1] || parts[0] || '';
        document.getElementById('adminNewFirstName').value = firstName;
        document.getElementById('adminNewLastName').value = lastName;

        generateUsernameFromInputs();
    }
}

function initResidentSelect2() {
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 === 'function') {
        const $ = window.jQuery;
        const $select = $('#adminResidentSelect');
        if ($select.data('select2')) {
            $select.select2('destroy');
        }
        $select.select2({
            dropdownParent: $('#createAdminModal'),
            placeholder: "-- Rezident talabani qidirish va tanlash --",
            allowClear: true,
            width: '100%'
        });

        $select.off('select2:select select2:clear change.residentFill')
               .on('select2:select change.residentFill', function() {
                   fillResidentData(this);
               });

        $select.on('select2:clear', function() {
            document.getElementById('adminNewFirstName').value = '';
            document.getElementById('adminNewLastName').value = '';
            document.getElementById('adminNewPhone').value = '';
            document.getElementById('adminNewUsername').value = '';
        });
    }
}

function openCreateAdminModal() {
    document.getElementById('createAdminForm').reset();
    generateRandomPassword();
    document.getElementById('createAdminModal').classList.remove('hidden');

    // Initialize or reset Select2 inside modal
    setTimeout(() => {
        initResidentSelect2();
        if (typeof window.jQuery !== 'undefined') {
            window.jQuery('#adminResidentSelect').val('').trigger('change.select2');
        }
    }, 60);
}

function closeCreateAdminModal() {
    document.getElementById('createAdminModal').classList.add('hidden');
    if (typeof window.jQuery !== 'undefined' && window.jQuery('#adminResidentSelect').data('select2')) {
        window.jQuery('#adminResidentSelect').val('').trigger('change.select2');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initResidentSelect2();
});

// Create Admin form submit
document.getElementById('createAdminForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitCreateAdminBtn');
    btn.disabled = true;

    try {
        const fd = new FormData(this);
        const res = await fetch('../insert/admin.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Muvaffaqiyatli!', text: data.message, timer: 1500, showConfirmButton: false });
            setTimeout(() => location.reload(), 1000);
        } else {
            Swal.fire({ icon: 'error', title: 'Xatolik', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Xatolik', text: 'Tarmoq xatosi yuz berdi' });
    } finally {
        btn.disabled = false;
    }
});

// Toggle Admin Status (Block / Unblock)
async function toggleAdminStatus(adminId, newStatus) {
    const actionText = newStatus === 'blocked' ? 'bloklamoqchimisiz' : 'blokdan chiqarmoqchimisiz';
    const result = await Swal.fire({
        title: 'Tasdiqlang',
        text: `Haqiqatan ham ushbu adminni ${actionText}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ha, tasdiqlayman',
        cancelButtonText: 'Bekor qilish'
    });
    if (!result.isConfirmed) return;

    try {
        const fd = new FormData();
        fd.append('admin_id', adminId);
        fd.append('status', newStatus);

        const res = await fetch('../update/admin_status.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Muvaffaqiyatli!', text: data.message, timer: 1200, showConfirmButton: false });
            setTimeout(() => location.reload(), 900);
        } else {
            Swal.fire({ icon: 'error', title: 'Xatolik', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Xatolik', text: 'Server bilan aloqa uzildi.' });
    }
}

// Reset Password Modal
function openResetPasswordModal(adminId, username) {
    document.getElementById('resetAdminId').value = adminId;
    document.getElementById('resetUsernameSpan').textContent = '@' + username;
    generateResetPassword();
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
}

document.getElementById('resetPasswordForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    try {
        const fd = new FormData(this);
        const res = await fetch('../update/admin_reset_password.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Parol yangilandi!', text: data.message });
            closeResetPasswordModal();
        } else {
            Swal.fire({ icon: 'error', title: 'Xatolik', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Xatolik', text: 'Tarmoq xatosi.' });
    }
});

// Delete Admin
async function deleteAdmin(adminId, username) {
    const result = await Swal.fire({
        title: 'Adminni o\'chirish',
        text: `@${username} adminini butunlay o'chirmoqchimisiz?`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Ha, o\'chirilsin',
        cancelButtonText: 'Bekor qilish',
        confirmButtonColor: '#ef4444'
    });
    if (!result.isConfirmed) return;

    try {
        const fd = new FormData();
        fd.append('id', adminId);

        const res = await fetch('../delete/admin.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'O\'chirildi!', text: data.message, timer: 1200, showConfirmButton: false });
            setTimeout(() => location.reload(), 900);
        } else {
            Swal.fire({ icon: 'error', title: 'Xatolik', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Xatolik', text: 'Tarmoq xatosi.' });
    }
}
</script>
