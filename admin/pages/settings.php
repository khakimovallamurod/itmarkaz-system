<?php
$admin = $pageData['admin'] ?? [];
$adminDisplayName = !empty($admin['first_name']) 
    ? trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? ''))
    : ($admin['username'] ?? 'Admin');
$adminInitial = strtoupper(substr(!empty($admin['first_name']) ? $admin['first_name'] : ($admin['username'] ?? 'A'), 0, 1));
?>

<div class="w-full space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5">
                <span class="h-9 w-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-user-gear"></i>
                </span>
                Sozlamalar va Profil
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">Admin shaxsiy ma'lumotlari, familiya, ism, telefon raqami va login parametrlarini boshqarish.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: Profile Card -->
        <div class="lg:col-span-4 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col items-center text-center">
            <div class="relative">
                <div class="h-28 w-28 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white font-bold text-5xl flex items-center justify-center shadow-lg shadow-emerald-200">
                    <?= $adminInitial; ?>
                </div>
                <span class="absolute -bottom-1 -right-1 h-7 w-7 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white text-xs" title="Faol administrator">
                    <i class="fa-solid fa-check"></i>
                </span>
            </div>

            <h3 id="profileCardName" class="text-xl font-bold text-slate-800 mt-4"><?= htmlspecialchars($adminDisplayName); ?></h3>
            <p id="profileCardUsername" class="text-sm text-slate-500 font-mono">@<?= htmlspecialchars($admin['username'] ?? 'admin'); ?></p>

            <div class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-100">
                <i class="fa-solid fa-shield-halved text-xs"></i>
                Bosh Administrator
            </div>

            <div class="w-full border-t border-slate-100 mt-6 pt-5 space-y-3.5 text-left text-sm">
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-xs text-slate-400 flex items-center gap-2">
                        <i class="fa-solid fa-phone text-slate-400 text-xs"></i> Telefon
                    </span>
                    <span id="profileCardPhone" class="font-semibold text-xs text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg">
                        <?= htmlspecialchars(!empty($admin['phone']) ? $admin['phone'] : 'Kiritilmagan'); ?>
                    </span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-xs text-slate-400 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-slate-400 text-xs"></i> Ro'yxatdan o'tgan
                    </span>
                    <span class="font-medium text-xs text-slate-700">
                        <?= !empty($admin['created_at']) ? date('d.m.Y', strtotime($admin['created_at'])) : '-'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Column: Settings Form -->
        <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8">
            <form id="adminProfileForm" class="space-y-6">
                <!-- Section: Personal Info -->
                <div>
                    <h4 class="text-lg font-bold text-slate-800 flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="fa-solid fa-address-card text-emerald-600"></i>
                        Shaxsiy ma'lumotlar
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="adminFirstName">
                                Ism <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-regular fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="adminFirstName" name="first_name" 
                                       value="<?= htmlspecialchars($admin['first_name'] ?? ''); ?>" 
                                       placeholder="Masalan: Allamurod" 
                                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="adminLastName">
                                Familiya
                            </label>
                            <div class="relative">
                                <i class="fa-regular fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="adminLastName" name="last_name" 
                                       value="<?= htmlspecialchars($admin['last_name'] ?? ''); ?>" 
                                       placeholder="Masalan: Xakimov" 
                                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="adminPhone">
                                Telefon raqami <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="adminPhone" name="phone" 
                                       value="<?= htmlspecialchars($admin['phone'] ?? ''); ?>" 
                                       placeholder="+998 90 123 45 67" 
                                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="adminUsername">
                                Login (foydalanuvchi nomi) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-at absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="adminUsername" name="username" 
                                       value="<?= htmlspecialchars($admin['username'] ?? ''); ?>" 
                                       placeholder="admin" 
                                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" id="saveProfileBtn" class="px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-sm hover:shadow-emerald-200 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>O'zgarishlarni saqlash</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const form = document.getElementById('adminProfileForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('saveProfileBtn');
        const origBtnText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saqlanmoqda...';

        try {
            const formData = new FormData(form);
            const res = await fetch('../update/admin_profile.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Muvaffaqiyatli!',
                    text: data.message || 'Ma\'lumotlar saqlandi.',
                    timer: 1500,
                    showConfirmButton: false
                });

                // Update UI elements
                const fullName = ((data.data?.first_name || '') + ' ' + (data.data?.last_name || '')).trim();
                const cleanName = fullName || data.data?.username || 'Admin';
                
                const cardName = document.getElementById('profileCardName');
                if (cardName) cardName.textContent = cleanName;

                const cardUser = document.getElementById('profileCardUsername');
                if (cardUser) cardUser.textContent = '@' + (data.data?.username || 'admin');

                const cardPhone = document.getElementById('profileCardPhone');
                if (cardPhone) cardPhone.textContent = data.data?.phone || 'Kiritilmagan';

                // Reload after short delay to update header
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Xatolik!',
                    text: data.message || 'Ma\'lumotlarni saqlashda xatolik yuz berdi.'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Tarmoq xatosi',
                text: 'Server bilan aloqa o\'rnatishda xatolik yuz berdi.'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = origBtnText;
        }
    });
})();
</script>
