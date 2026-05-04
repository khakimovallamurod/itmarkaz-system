<?php
$currentPage = $currentPage ?? 'dashboard';
$menus = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'fa-home', 'link' => 'index.php?page=dashboard'],
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
];
$isSettingsSection = in_array($currentPage, array_keys($settingsChildren), true);
$activePage = $currentPage === 'competition_detail' ? 'competitions' : $currentPage;
?>
<aside
    id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-72 md:w-72 bg-gradient-to-b from-green-900 to-green-800 text-emerald-50 transform -translate-x-full md:translate-x-0 transition-all duration-200 shadow-2xl">
    <div class="h-full flex flex-col">
        <div class="px-4 py-5 border-b border-emerald-900/60 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-emerald-200/20 text-emerald-100 flex items-center justify-center font-bold">IT</div>
                <div class="sidebar-label">
                    <p class="text-sm text-emerald-200">IT-Markaz</p>
                    <p class="text-lg font-semibold leading-tight text-emerald-50">Admin Suite</p>
                </div>
            </div>
            <button id="sidebarCollapseBtn" class="hidden md:flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-900/30 text-emerald-100 hover:bg-emerald-700/90 transition-all duration-200" title="Sidebarni yig'ish">
                <i class="fa-solid fa-bars-staggered text-sm"></i>
            </button>
        </div>
        <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
            <?php foreach ($menus as $key => $menu): ?>
                <a
                    href="<?= $menu['link']; ?>"
                    class="sidebar-link group relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 <?= $activePage === $key ? 'bg-emerald-300 text-emerald-950 shadow-md' : 'hover:bg-emerald-800/80 text-emerald-50'; ?>"
                    data-tooltip="<?= htmlspecialchars($menu['label']); ?>">
                    <i class="fa-solid <?= $menu['icon']; ?> w-5 text-center"></i>
                    <span class="sidebar-label whitespace-nowrap"><?= $menu['label']; ?></span>
                </a>
            <?php endforeach; ?>

            <div class="space-y-1">
                <button
                    id="settingsMenuToggle"
                    type="button"
                    class="sidebar-link group relative flex w-full items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 <?= $isSettingsSection ? 'bg-emerald-300 text-emerald-950 shadow-md' : 'hover:bg-emerald-800/80 text-emerald-50'; ?>"
                    aria-expanded="<?= $isSettingsSection ? 'true' : 'false'; ?>">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-gears w-5 text-center"></i>
                        <span class="sidebar-label whitespace-nowrap">Sozlamalar</span>
                    </span>
                    <i id="settingsMenuChevron" class="fa-solid fa-chevron-down text-xs sidebar-label transition-transform duration-200 <?= $isSettingsSection ? 'rotate-180' : ''; ?>"></i>
                </button>
                <div id="settingsSubmenu" class="space-y-1 pl-5 <?= $isSettingsSection ? '' : 'hidden'; ?>">
                    <?php foreach ($settingsChildren as $key => $item): ?>
                        <a
                            href="<?= $item['link']; ?>"
                            class="sidebar-link group relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 <?= $currentPage === $key ? 'bg-emerald-200 text-emerald-950 shadow-sm' : 'hover:bg-emerald-800/70 text-emerald-100'; ?>"
                            data-tooltip="<?= htmlspecialchars($item['label']); ?>">
                            <i class="fa-solid <?= $item['icon']; ?> w-5 text-center"></i>
                            <span class="sidebar-label whitespace-nowrap text-sm"><?= $item['label']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </nav>
    </div>
</aside>
