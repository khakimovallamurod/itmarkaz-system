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
    <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- 1. Jami talabalar -->
        <a href="index.php?page=students" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-blue-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jami talabalar</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['students'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-blue-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-users text-6xl"></i>
            </div>
        </a>

        <!-- 2. Rezidentlar -->
        <a href="index.php?page=residents" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-indigo-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rezidentlar</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['residents'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-building-user text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-indigo-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-building-user text-6xl"></i>
            </div>
        </a>

        <!-- 3. Mentorlar -->
        <a href="index.php?page=mentors" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-orange-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mentorlar</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['mentors'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-chalkboard-user text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-orange-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-chalkboard-user text-6xl"></i>
            </div>
        </a>

        <!-- 4. Kurs o'quvchilari -->
        <a href="index.php?page=course_students" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-emerald-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kurs o'quvchilari</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['course_students'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-graduation-cap text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-emerald-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-graduation-cap text-6xl"></i>
            </div>
        </a>

        <!-- 5. Jami kurslar -->
        <a href="index.php?page=courses" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-purple-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jami kurslar</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['courses_count'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-book-open text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-purple-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-book-open text-6xl"></i>
            </div>
        </a>

        <!-- 6. Bitiruvchilar soni -->
        <a href="index.php?page=course_students&status=completed" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-teal-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kurs bitiruvchilari</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['graduates_count'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-graduate text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-teal-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-user-graduate text-6xl"></i>
            </div>
        </a>

        <!-- 7. Tanlovlar soni -->
        <a href="index.php?page=competitions" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-yellow-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanlovlar soni</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['competitions'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-trophy text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-yellow-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-trophy text-6xl"></i>
            </div>
        </a>

        <!-- 8. O'zini band qilganlar (Upwork) -->
        <a href="index.php?page=teams" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-cyan-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">O'zini band qilganlar</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['upwork_students_count'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-briefcase text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-cyan-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-briefcase text-6xl"></i>
            </div>
        </a>

        <!-- 9. Loyihalar ustida ishlayotganlar -->
        <a href="index.php?page=projects" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-rose-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Loyiha a'zolari</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['project_students_count'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-laptop-code text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-rose-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-laptop-code text-6xl"></i>
            </div>
        </a>

        <!-- 10. Tijorat shartnomalari -->
        <a href="index.php?page=payments" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group hover:border-emerald-200 transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tijorat shartnomalari</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($stats['commercial_contracts_count'] ?? 0); ?></h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-file-signature text-lg"></i>
                </div>
            </div>
            <div class="absolute -right-2 -bottom-2 text-emerald-50/30 group-hover:scale-110 transition-transform duration-500">
                <i class="fa-solid fa-file-signature text-6xl"></i>
            </div>
        </a>
    </section>

    <!-- Main Chart Area (Full Width) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col w-full">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
            <div>
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-blue-600"></i>
                    Talabalar statistikasi (Jonli)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Tizimga qo'shilgan talabalar o'sish dinamikasi</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <div class="flex bg-slate-100 p-1 rounded-xl text-xs font-semibold">
                    <button id="chartModeMonthly" type="button" class="px-3.5 py-1.5 rounded-lg bg-white text-blue-600 shadow-sm transition-all">Oylik</button>
                    <button id="chartModeDaily" type="button" class="px-3.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 transition-all font-normal">Kunlik</button>
                </div>
            </div>
        </div>

        <!-- Status Date Header matching sample -->
        <div class="text-center my-3">
            <span class="inline-block text-xs md:text-sm font-bold text-slate-800 tracking-wide">
                Holati: <?= !empty($stats['student_timeline']['today_str']) ? htmlspecialchars($stats['student_timeline']['today_str']) : date('Y-m-d l d-F'); ?>
            </span>
        </div>

        <div class="flex-1 min-h-[340px] relative">
            <canvas id="dashboardChart"></canvas>
        </div>
    </div>

    <!-- Bottom Section: Competitions, Course Dist & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
    
    const timeline = <?= json_encode($stats['student_timeline'] ?? [
        'monthly' => ['labels' => [], 'values' => []],
        'daily' => ['labels' => [], 'values' => []]
    ], JSON_UNESCAPED_UNICODE); ?>;

    const canvas = ctx.getContext('2d');
    let gradient = canvas.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.35)');
    gradient.addColorStop(0.65, 'rgba(59, 130, 246, 0.10)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.00)');

    let currentMode = 'monthly';

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: timeline.monthly?.labels || [],
            datasets: [{
                label: 'Kelib tushgan talabalar',
                data: timeline.monthly?.values || [],
                borderColor: '#3b82f6',
                borderWidth: 2.8,
                backgroundColor: gradient,
                fill: true,
                tension: 0.45,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2.5,
                pointHoverBackgroundColor: '#2563eb',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#ffffff',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyColor: '#e2e8f0',
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: true,
                    boxWidth: 10,
                    boxHeight: 10,
                    callbacks: {
                        label: function(context) {
                            return ' Kelib tushgan talabalar: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });

    const btnMonthly = document.getElementById('chartModeMonthly');
    const btnDaily = document.getElementById('chartModeDaily');

    function updateMode(mode) {
        if (currentMode === mode) return;
        currentMode = mode;
        if (mode === 'monthly') {
            btnMonthly.className = 'px-3.5 py-1.5 rounded-lg bg-white text-blue-600 shadow-sm transition-all font-semibold';
            btnDaily.className = 'px-3.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 transition-all font-normal';
            chart.data.labels = timeline.monthly?.labels || [];
            chart.data.datasets[0].data = timeline.monthly?.values || [];
        } else {
            btnDaily.className = 'px-3.5 py-1.5 rounded-lg bg-white text-blue-600 shadow-sm transition-all font-semibold';
            btnMonthly.className = 'px-3.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 transition-all font-normal';
            chart.data.labels = timeline.daily?.labels || [];
            chart.data.datasets[0].data = timeline.daily?.values || [];
        }
        chart.update();
    }

    btnMonthly?.addEventListener('click', () => updateMode('monthly'));
    btnDaily?.addEventListener('click', () => updateMode('daily'));

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
