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

            <div class="relative">
                <button id="profileMenuBtn" class="flex items-center gap-2 rounded-xl border border-gray-200 px-2 py-1.5 text-green-800 hover:bg-green-50 transition-all duration-200">
                    <div class="h-8 w-8 rounded-full bg-green-100 text-green-700 font-semibold text-sm flex items-center justify-center">
                        <?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-green-800">admin</span>
                    <i class="fa-solid fa-chevron-down text-xs text-green-700"></i>
                </button>
                <div id="profileDropdown" class="hidden pointer-events-none absolute right-0 mt-2 w-44 rounded-xl border border-gray-200 bg-white shadow-lg p-1 opacity-0 translate-y-1 transition-all duration-200">
                    <button class="w-full text-left px-3 py-2 rounded-lg text-sm hover:bg-gray-100 transition-all duration-200">Profile</button>
                    <button class="w-full text-left px-3 py-2 rounded-lg text-sm hover:bg-gray-100 transition-all duration-200">Settings</button>
                    <button id="logoutBtn" class="w-full text-left px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50 transition-all duration-200">Logout</button>
                </div>
            </div>
        </div>
    </div>
</header>
