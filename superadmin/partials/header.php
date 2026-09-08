<?php
$unreadNotifications = get_unread_notifications_count($db);
$superDisplayName = !empty($_SESSION['admin_first_name']) 
    ? trim($_SESSION['admin_first_name'] . ' ' . ($_SESSION['admin_last_name'] ?? ''))
    : ($_SESSION['admin_username'] ?? 'Super');
$superInitial = strtoupper(substr(!empty($_SESSION['admin_first_name']) ? $_SESSION['admin_first_name'] : ($_SESSION['admin_username'] ?? 'S'), 0, 1));
?>
<header class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
    <div class="px-4 md:px-6 py-3 flex items-center justify-between gap-3">
        <!-- Left: Page Title -->
        <div class="flex items-center gap-3 min-w-0">
            <button id="sidebarToggle" class="md:hidden h-10 w-10 flex items-center justify-center border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-all duration-200">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="min-w-0 flex items-center gap-2.5">
                <span class="h-9 w-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-gauge-high"></i>
                </span>
                <div>
                    <h1 class="font-bold text-slate-900 text-base md:text-lg truncate"><?= htmlspecialchars($pageTitle ?? 'Boshqaruv paneli'); ?></h1>
                    <p class="text-xs text-slate-500 truncate hidden sm:block"><?= htmlspecialchars($pageSubtitle ?? 'Super Admin tizimi'); ?></p>
                </div>
            </div>
        </div>

        <!-- Right: Actions & Profile -->
        <div class="flex items-center gap-2 md:gap-3">
            <!-- Global Search -->
            <div class="hidden lg:block relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input id="globalSearch" class="w-48 xl:w-60 bg-gray-50 border border-gray-200 rounded-full px-4 py-2 pl-9 text-xs focus:bg-white focus:border-emerald-500 outline-none transition-all" placeholder="Qidiruv...">
            </div>

            <!-- Notifications Bell -->
            <a href="index.php?page=notifications" id="headerBellLink" class="h-10 w-10 rounded-xl border border-gray-200 bg-white text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all duration-200 relative flex items-center justify-center" title="Bildirishnomalar">
                <i class="fa-regular fa-bell text-sm"></i>
                <span id="headerUnreadBadge" 
                      class="absolute -top-1 -right-1 h-5 min-w-[20px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold items-center justify-center shadow-sm animate-pulse <?= $unreadNotifications > 0 ? 'flex' : 'hidden'; ?>"
                      style="<?= $unreadNotifications > 0 ? '' : 'display: none;'; ?>">
                    <?= $unreadNotifications > 99 ? '99+' : $unreadNotifications; ?>
                </span>
            </a>

            <!-- Date Box (Sample style) -->
            <div class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-slate-700 text-xs font-semibold">
                <i class="fa-regular fa-calendar text-slate-400"></i>
                <span><?= date('d.m.Y'); ?></span>
            </div>

            <!-- Super Admin Badge Dropdown -->
            <div class="relative">
                <button id="profileMenuBtn" class="flex items-center gap-2.5 rounded-xl border border-gray-200 bg-white px-2.5 py-1.5 hover:bg-emerald-50 hover:border-emerald-200 transition-all duration-200">
                    <div class="h-8 w-8 rounded-full bg-emerald-500 text-white font-bold text-sm flex items-center justify-center shadow-sm">
                        <?= $superInitial; ?>
                    </div>
                    <div class="hidden sm:flex flex-col text-left leading-tight">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600">SUPER ADMIN</span>
                        <span class="text-xs font-bold text-slate-800 max-w-[110px] truncate"><?= htmlspecialchars($superDisplayName); ?></span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-0.5"></i>
                </button>

                <div id="profileDropdown" class="hidden pointer-events-none absolute right-0 mt-2 w-52 rounded-xl border border-gray-200 bg-white shadow-xl p-1 opacity-0 translate-y-1 transition-all duration-200 z-50">
                    <div class="px-3 py-2 border-b border-gray-100 mb-1">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 block">SUPER ADMIN</span>
                        <p class="text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars($superDisplayName); ?></p>
                        <p class="text-[11px] text-slate-400 truncate font-mono">@<?= htmlspecialchars($_SESSION['admin_username'] ?? 'superadmin'); ?></p>
                    </div>
                    <a href="index.php?page=admins" class="flex items-center gap-2.5 w-full text-left px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-all">
                        <i class="fa-solid fa-users-gear text-slate-400 text-xs"></i> Adminlar boshqaruvi
                    </a>
                    <a href="index.php?page=notifications" class="flex items-center justify-between w-full text-left px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-all">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-bell text-slate-400 text-xs"></i> Bildirishnomalar
                        </span>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="h-4 px-1.5 rounded-full bg-rose-500 text-white text-[10px] font-bold"><?= $unreadNotifications; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="index.php?page=settings" class="flex items-center gap-2.5 w-full text-left px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-all">
                        <i class="fa-solid fa-gear text-slate-400 text-xs"></i> Sozlamalar
                    </a>
                    <div class="my-1 border-t border-gray-100"></div>
                    <a href="../admin/index.php" class="flex items-center gap-2.5 w-full text-left px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-all">
                        <i class="fa-solid fa-arrow-right-to-bracket text-slate-400 text-xs"></i> Oddiy Admin panelga o'tish
                    </a>
                    <div class="my-1 border-t border-gray-100"></div>
                    <button id="logoutBtn" class="flex items-center gap-2.5 w-full text-left px-3 py-2 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 transition-all">
                        <i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-xs"></i> Chiqish
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
