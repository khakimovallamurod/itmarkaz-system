<?php $stats = $pageData['stats'] ?? []; ?>
<div class="space-y-6">
    <!-- Quick Overview Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Xush kelibsiz, Admin!</h2>
            <p class="text-slate-500">Bugungi tizim holati va asosiy ko'rsatkichlar.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="index.php?page=students" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl shadow-sm hover:bg-emerald-700 transition-all font-medium text-sm">
                <i class="fa-solid fa-user-plus"></i>
                Yangi talaba
            </a>
            <a href="index.php?page=payments" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 border border-slate-200 rounded-xl shadow-sm hover:bg-slate-50 transition-all font-medium text-sm">
                <i class="fa-solid fa-money-bill-transfer"></i>
                To'lov qo'shish
            </a>
        </div>
    </div>

    <!-- Main Stats Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jami talabalar</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= number_format($stats['students'] ?? 0); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 relative z-10">
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Aktiv</span>
                <span class="text-xs text-slate-400">Hozirgi vaqtda</span>
            </div>
            <div class="absolute -right-4 -bottom-4 text-blue-50/50 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-users text-8xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Umumiy tushum</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= number_format($stats['total_payments'] ?? 0, 0, '.', ' '); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-sack-dollar text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 relative z-10">
                <span class="text-xs text-slate-500">UZS</span>
            </div>
            <div class="absolute -right-4 -bottom-4 text-emerald-50/50 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-sack-dollar text-8xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rezidentlar</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= number_format($stats['residents'] ?? 0); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fa-solid fa-building-user text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 relative z-10">
                <span class="text-xs text-slate-500">Doimiy</span>
            </div>
            <div class="absolute -right-4 -bottom-4 text-indigo-50/50 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-building-user text-8xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Mentorlar</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= number_format($stats['mentors'] ?? 0); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                    <i class="fa-solid fa-chalkboard-user text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 relative z-10">
                <span class="text-xs text-slate-500">Mutaxassislar</span>
            </div>
            <div class="absolute -right-4 -bottom-4 text-orange-50/50 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-chalkboard-user text-8xl"></i>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Chart Area -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-emerald-500"></i>
                    Natijalar statistikasi
                </h3>
                <div class="flex gap-1 bg-slate-100 p-1 rounded-lg">
                    <button class="px-3 py-1 text-xs font-medium rounded-md bg-white shadow-sm">Natijalar</button>
                </div>
            </div>
            <div class="flex-1 min-h-[300px] relative">
                <canvas id="dashboardChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-bolt text-orange-400"></i>
                Oxirgi faolliklar
            </h3>
            <div class="space-y-6 relative before:absolute before:left-[17px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100">
                <?php foreach(($stats['recent_activity'] ?? []) as $act): 
                    $iconClass = 'bg-slate-100 text-slate-600';
                    $icon = 'fa-circle-dot';
                    switch($act['activity_type'] ?? '') {
                        case 'student': $iconClass = 'bg-blue-100 text-blue-600'; $icon = 'fa-user-plus'; break;
                        case 'payment': $iconClass = 'bg-emerald-100 text-emerald-600'; $icon = 'fa-money-check-dollar'; break;
                        case 'competition': $iconClass = 'bg-orange-100 text-orange-600'; $icon = 'fa-trophy'; break;
                        case 'result': $iconClass = 'bg-indigo-100 text-indigo-600'; $icon = 'fa-award'; break;
                        case 'project': $iconClass = 'bg-purple-100 text-purple-600'; $icon = 'fa-laptop-code'; break;
                    }
                ?>
                    <div class="flex gap-4 relative">
                        <div class="h-9 w-9 shrink-0 rounded-full flex items-center justify-center z-10 border-4 border-white <?= $iconClass; ?>">
                            <i class="fa-solid <?= $icon; ?> text-[10px]"></i>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-sm font-medium text-slate-800 leading-tight"><?= htmlspecialchars($act['title']); ?></p>
                            <p class="text-xs text-slate-400 mt-1"><?= date('H:i, d-M', strtotime($act['created_at'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($stats['recent_activity'])): ?>
                    <p class="text-center text-sm text-slate-400 py-10">Ma'lumot mavjud emas</p>
                <?php endif; ?>
            </div>
            <div class="mt-6">
                <button class="w-full py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                    Barchasini ko'rish
                </button>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Competitions & Course Dist -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Competitions -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar-star text-indigo-500"></i>
                    Yaqinlashayotgan tanlovlar
                </span>
                <a href="index.php?page=competitions" class="text-xs font-medium text-indigo-600 hover:underline">Barchasi</a>
            </h3>
            <div class="grid gap-3">
                <?php foreach(($stats['upcoming_competitions'] ?? []) as $c): ?>
                    <div class="flex items-center justify-between p-4 rounded-2xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/30 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 flex flex-col items-center justify-center">
                                <span class="text-xs font-bold leading-none"><?= date('d', strtotime($c['competition_date'])); ?></span>
                                <span class="text-[9px] uppercase font-bold"><?= date('M', strtotime($c['competition_date'])); ?></span>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 group-hover:text-indigo-700"><?= htmlspecialchars($c['name']); ?></h4>
                                <p class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-location-dot text-[10px]"></i>
                                    <?= htmlspecialchars($c['location'] ?: 'Onlayn'); ?>
                                </p>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-300 group-hover:text-indigo-400 transition-colors"></i>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($stats['upcoming_competitions'])): ?>
                    <p class="text-center text-sm text-slate-400 py-6">Yaqin orada tanlovlar yo'q</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Course Distribution -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-emerald-500"></i>
                Kurslar bo'yicha taqsimot
            </h3>
            <div class="space-y-4">
                <?php 
                $maxCourseCnt = 1;
                foreach($stats['course_distribution'] as $cd) if ($cd['cnt'] > $maxCourseCnt) $maxCourseCnt = $cd['cnt'];
                foreach(($stats['course_distribution'] ?? []) as $cd): 
                    $percent = round(($cd['cnt'] / $maxCourseCnt) * 100);
                ?>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($cd['name']); ?></span>
                            <span class="text-xs font-bold text-slate-500"><?= (int)$cd['cnt']; ?> talaba</span>
                        </div>
                        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: <?= $percent; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($stats['course_distribution'])): ?>
                    <p class="text-center text-sm text-slate-400 py-6">Ma'lumot mavjud emas</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const ctx = document.getElementById('dashboardChart');
    if (!ctx) return;
    
    const dist = <?= json_encode($stats['result_distribution'] ?? []); ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['1-o\'rin', '2-o\'rin', '3-o\'rin'],
            datasets: [{
                label: 'Natijalar soni',
                data: [dist.first || 0, dist.second || 0, dist.third || 0],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
                borderRadius: 12,
                maxBarThickness: 45
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // Project Status Chart (Dashboard)
    const psCtx = document.getElementById('projectStatusChart');
    if (psCtx) {
        const psData = <?= json_encode($stats['projects_by_status'] ?? []); ?>;
        new Chart(psCtx, {
            type: 'doughnut',
            data: {
                labels: psData.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1)),
                datasets: [{
                    data: psData.map(d => d.cnt),
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } },
                cutout: '65%'
            }
        });
    }
})();
</script>
