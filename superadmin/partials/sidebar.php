<?php
$currentPage = $currentPage ?? 'dashboard';
$unreadCount = get_unread_notifications_count($db);

$superMenus = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'fa-home', 'link' => 'index.php?page=dashboard'],
    'admins' => ['label' => 'Adminlar', 'icon' => 'fa-users-gear', 'link' => 'index.php?page=admins', 'badge' => 'Boshqaruv'],
    'notifications' => ['label' => 'Bildirishnomalar', 'icon' => 'fa-bell', 'link' => 'index.php?page=notifications', 'count' => $unreadCount],
    'students' => ['label' => 'Talabalar', 'icon' => 'fa-users', 'link' => 'index.php?page=students'],
    'residents' => ['label' => 'Rezidentlar', 'icon' => 'fa-user-check', 'link' => 'index.php?page=residents'],
    'course_students' => ['label' => 'Kurs o\'quvchilar', 'icon' => 'fa-graduation-cap', 'link' => 'index.php?page=course_students'],
    'rooms' => ['label' => 'Xonalar', 'icon' => 'fa-door-open', 'link' => 'index.php?page=rooms'],
    'courses' => ['label' => 'Kurslar', 'icon' => 'fa-book-open', 'link' => 'index.php?page=courses'],
    'mentors' => ['label' => 'Mentorlar', 'icon' => 'fa-chalkboard-teacher', 'link' => 'index.php?page=mentors'],
    'competitions' => ['label' => 'Tanlovlar', 'icon' => 'fa-trophy', 'link' => 'index.php?page=competitions'],
    'schedule' => ['label' => 'Ish jadvali', 'icon' => 'fa-calendar-days', 'link' => 'index.php?page=schedule'],
    'teams' => ['label' => 'Upwork Jamoa', 'icon' => 'fa-people-group', 'link' => 'index.php?page=teams'],
    'projects' => ['label' => 'Loyihalar', 'icon' => 'fa-diagram-project', 'link' => 'index.php?page=projects'],
    'payments' => ['label' => 'Tijorat shartnomalar', 'icon' => 'fa-hand-holding-dollar', 'link' => 'index.php?page=payments'],
    'statistics' => ['label' => 'Statistika', 'icon' => 'fa-chart-simple', 'link' => 'index.php?page=statistics'],
];

$settingsChildren = [
    'directions' => ['label' => 'Yo\'nalishlar', 'icon' => 'fa-folder-tree', 'link' => 'index.php?page=directions'],
    'statuses' => ['label' => 'Statuslar', 'icon' => 'fa-list-check', 'link' => 'index.php?page=statuses'],
    'settings' => ['label' => 'Profil sozlamalari', 'icon' => 'fa-user-gear', 'link' => 'index.php?page=settings'],
];
$isSettingsSection = in_array($currentPage, array_keys($settingsChildren), true);
$activePage = $currentPage === 'competition_detail' ? 'competitions' : $currentPage;
?>
<aside
    id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-72 md:w-72 bg-gradient-to-b from-slate-900 via-emerald-950 to-slate-900 text-emerald-50 transform -translate-x-full md:translate-x-0 transition-all duration-200 shadow-2xl">
    <div class="h-full flex flex-col">
        <!-- Logo -->
        <div class="px-4 py-5 border-b border-emerald-900/60 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-300 text-slate-950 flex items-center justify-center font-black text-sm shadow-md shadow-emerald-500/30">IT</div>
                <div class="sidebar-label">
                    <div class="flex items-center gap-1.5">
                        <p class="text-sm font-bold text-white tracking-wide">IT-Markaz</p>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500 text-slate-950 font-extrabold uppercase tracking-wider">Super</span>
                    </div>
                    <p class="text-[11px] text-emerald-400 font-medium">Boshqaruv Tizimi</p>
                </div>
            </div>
            <button id="sidebarCollapseBtn" class="hidden md:flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-900/30 text-emerald-100 hover:bg-emerald-700/90 transition-all duration-200" title="Sidebarni yig'ish">
                <i class="fa-solid fa-bars-staggered text-sm"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
            <?php foreach ($superMenus as $key => $menu): ?>
                <a
                    href="<?= $menu['link']; ?>"
                    class="sidebar-link group relative flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === $key ? 'bg-emerald-400 text-slate-950 font-semibold shadow-lg shadow-emerald-400/20' : 'hover:bg-white/10 text-slate-200'; ?>"
                    data-tooltip="<?= htmlspecialchars($menu['label']); ?>">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid <?= $menu['icon']; ?> w-5 text-center text-sm <?= $activePage === $key ? 'text-slate-950' : 'text-emerald-400 group-hover:text-emerald-300'; ?>"></i>
                        <span class="sidebar-label whitespace-nowrap text-sm"><?= $menu['label']; ?></span>
                    </span>
                    <?php if (!empty($menu['badge'])): ?>
                        <span class="sidebar-label text-[10px] px-2 py-0.5 rounded-full <?= $activePage === $key ? 'bg-slate-950 text-emerald-300' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'; ?> font-bold uppercase tracking-wider">
                            <?= $menu['badge']; ?>
                        </span>
                    <?php endif; ?>
                    <?php 
                    $showCount = isset($menu['count']) && (int) $menu['count'] > 0;
                    ?>
                    <span id="<?= $key === 'notifications' ? 'sidebarUnreadBadge' : ''; ?>" 
                          class="sidebar-label h-5 min-w-[20px] px-1.5 rounded-full bg-rose-500 text-white text-[10px] font-bold items-center justify-center animate-pulse shadow-sm <?= $showCount ? 'flex' : 'hidden'; ?>"
                          style="<?= $showCount ? '' : 'display: none;'; ?>">
                        <?= (int) ($menu['count'] ?? 0) > 99 ? '99+' : (int) ($menu['count'] ?? 0); ?>
                    </span>
                </a>
            <?php endforeach; ?>

            <div class="space-y-1 pt-2">
                <button
                    id="settingsMenuToggle"
                    type="button"
                    class="sidebar-link group relative flex w-full items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 <?= $isSettingsSection ? 'bg-emerald-400 text-slate-950 font-semibold shadow-md' : 'hover:bg-white/10 text-slate-200'; ?>"
                    aria-expanded="<?= $isSettingsSection ? 'true' : 'false'; ?>">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-gears w-5 text-center text-emerald-400 <?= $isSettingsSection ? 'text-slate-950' : ''; ?>"></i>
                        <span class="sidebar-label whitespace-nowrap text-sm">Sozlamalar</span>
                    </span>
                    <i id="settingsMenuChevron" class="fa-solid fa-chevron-down text-xs sidebar-label transition-transform duration-200 <?= $isSettingsSection ? 'rotate-180 text-slate-950' : 'text-slate-400'; ?>"></i>
                </button>
                <div id="settingsSubmenu" class="space-y-1 pl-5 <?= $isSettingsSection ? '' : 'hidden'; ?>">
                    <?php foreach ($settingsChildren as $key => $item): ?>
                        <a
                            href="<?= $item['link']; ?>"
                            class="sidebar-link group relative flex items-center gap-2.5 px-3 py-2 rounded-lg transition-all duration-200 <?= $currentPage === $key ? 'bg-emerald-300 text-slate-950 font-semibold shadow-sm' : 'hover:bg-white/10 text-slate-300'; ?>"
                            data-tooltip="<?= htmlspecialchars($item['label']); ?>">
                            <i class="fa-solid <?= $item['icon']; ?> w-4 text-center text-xs"></i>
                            <span class="sidebar-label whitespace-nowrap text-xs"><?= $item['label']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </nav>

        <!-- Bottom Session Info -->
        <div class="p-3 border-t border-white/10">
            <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-white/5 text-xs text-emerald-300">
                <span class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="font-medium text-slate-300">Tizim holati: Faol</span>
                </span>
            </div>
        </div>
    </div>
</aside>
