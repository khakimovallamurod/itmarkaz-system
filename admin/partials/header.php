<header class="sticky top-0 z-20 bg-white border-b border-gray-200">
    <div class="px-4 md:px-6 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button id="sidebarToggle" class="md:hidden h-10 w-10 flex items-center justify-center border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-all duration-200">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="min-w-0">
                <h1 class="font-semibold text-slate-900 text-lg truncate"><?= htmlspecialchars($pageTitle ?? 'Dashboard'); ?></h1>
                <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($pageSubtitle ?? 'Admin panel'); ?></p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <div class="hidden md:block relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                <input id="globalSearch" class="w-44 lg:w-56 bg-gray-100 rounded-full px-4 py-2 pl-8 text-sm focus:ring-2 focus:ring-green-500 outline-none transition-all duration-200" placeholder="Qidiruv...">
                <div id="globalSearchResults" class="hidden absolute top-[110%] right-0 w-72 bg-white border border-gray-200 rounded-2xl shadow-lg p-2 max-h-72 overflow-auto"></div>
            </div>

            <div class="relative">
                <button id="calendarBtn" class="h-10 w-10 rounded-xl border border-gray-200 bg-white text-green-700 hover:bg-green-50 transition-all duration-200 relative">
                    <i class="fa-solid fa-calendar"></i>
                </button>
                <div id="calendarPopover" class="hidden absolute right-0 mt-2 w-[280px] bg-white border border-gray-200 rounded-xl shadow-lg p-2 z-50">
                    <input id="calendarInput" type="text" class="hidden">
                </div>
            </div>

            <button class="h-10 w-10 rounded-xl border border-gray-200 bg-white text-green-700 hover:bg-green-50 transition-all duration-200 relative">
                <i class="fa-regular fa-bell"></i>
                <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-rose-500"></span>
            </button>

            <?php
            $adminDisplayName = !empty($_SESSION['admin_first_name']) 
                ? trim($_SESSION['admin_first_name'] . ' ' . ($_SESSION['admin_last_name'] ?? ''))
                : ($_SESSION['admin_username'] ?? 'admin');
            $adminInitial = strtoupper(substr(!empty($_SESSION['admin_first_name']) ? $_SESSION['admin_first_name'] : ($_SESSION['admin_username'] ?? 'A'), 0, 1));
            ?>
            <div class="relative">
                <button id="profileMenuBtn" class="flex items-center gap-2 rounded-xl border border-gray-200 px-2 py-1.5 text-green-800 hover:bg-green-50 transition-all duration-200">
                    <div class="h-8 w-8 rounded-full bg-green-100 text-green-700 font-semibold text-sm flex items-center justify-center">
                        <?= $adminInitial; ?>
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-green-800 max-w-[130px] truncate"><?= htmlspecialchars($adminDisplayName); ?></span>
                    <i class="fa-solid fa-chevron-down text-xs text-green-700"></i>
                </button>
                <div id="profileDropdown" class="hidden pointer-events-none absolute right-0 mt-2 w-48 rounded-xl border border-gray-200 bg-white shadow-lg p-1 opacity-0 translate-y-1 transition-all duration-200 z-50">
                    <div class="px-3 py-2 border-b border-gray-100 mb-1">
                        <p class="text-xs font-semibold text-slate-800 truncate"><?= htmlspecialchars($adminDisplayName); ?></p>
                        <p class="text-[11px] text-slate-400 truncate">@<?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin'); ?></p>
                    </div>
                    <a href="index.php?page=settings" class="flex items-center gap-2 w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-all duration-200">
                        <i class="fa-regular fa-user text-xs text-slate-400"></i> Profile
                    </a>
                    <a href="index.php?page=settings" class="flex items-center gap-2 w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-all duration-200">
                        <i class="fa-solid fa-gear text-xs text-slate-400"></i> Settings
                    </a>
                    <div class="my-1 border-t border-gray-100"></div>
                    <button id="logoutBtn" class="flex items-center gap-2 w-full text-left px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50 transition-all duration-200">
                        <i class="fa-solid fa-arrow-right-from-bracket text-xs text-red-500"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
