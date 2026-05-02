<?php $stats = $pageData['stats'] ?? []; ?>
<div class="p-4 space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Analitika va Statistika</h2>
            <p class="text-slate-500 text-sm">Tizimdagi jarayonlarning chuqur tahlili va resurslar hisoboti.</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-200">
            <i class="fa-solid fa-calendar-check text-emerald-500"></i>
            <span class="text-sm font-medium text-slate-700"><?= date('d.m.Y'); ?> holatiga</span>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Financial Deep Dive -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-emerald-500"></i>
                To'lov turlari bo'yicha tahlil
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <div class="h-48 relative">
                    <canvas id="paymentTypeChart"></canvas>
                </div>
                <div class="space-y-3">
                    <?php foreach(($stats['payments_by_type'] ?? []) as $pt): ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-sm font-medium text-slate-600"><?= htmlspecialchars($pt['name']); ?></span>
                            <span class="text-sm font-bold text-slate-800"><?= number_format($pt['total'], 0, '.', ' '); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-between">
                <span class="text-sm font-medium text-emerald-800">Jami tushum:</span>
                <span class="text-lg font-black text-emerald-600"><?= number_format($stats['total_payments'] ?? 0, 0, '.', ' '); ?> UZS</span>
            </div>
        </div>

        <!-- Student Directions -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-compass text-blue-500"></i>
                Yo'nalishlar bo'yicha taqsimot
            </h3>
            <div class="h-64 relative">
                <canvas id="directionChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Room Occupancy -->
        <div class="xl:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-house-laptop text-indigo-500"></i>
                    Xonalar bandligi va resurslar
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-50">
                            <th class="text-left font-semibold pb-3">Xona</th>
                            <th class="text-center font-semibold pb-3">Komp.</th>
                            <th class="text-center font-semibold pb-3">Talabalar</th>
                            <th class="text-right font-semibold pb-3">Hajmi (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach(($stats['rooms_occupancy'] ?? []) as $room): 
                            $occ = $room['computers_count'] > 0 ? round(($room['residents_count'] / $room['computers_count']) * 100) : 0;
                            $barColor = $occ > 80 ? 'bg-rose-500' : ($occ > 40 ? 'bg-emerald-500' : 'bg-blue-500');
                        ?>
                            <tr>
                                <td class="py-4 font-bold text-slate-700"><?= htmlspecialchars($room['room_number']); ?>-xona</td>
                                <td class="py-4 text-center text-slate-500"><?= (int)$room['computers_count']; ?></td>
                                <td class="py-4 text-center">
                                    <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold"><?= (int)$room['residents_count']; ?></span>
                                </td>
                                <td class="py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="w-24 h-2 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                            <div class="h-full <?= $barColor; ?> rounded-full" style="width: <?= $occ; ?>%"></div>
                                        </div>
                                        <span class="font-bold <?= str_replace('bg-', 'text-', $barColor); ?>"><?= $occ; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Scoreboard -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-ranking-star text-orange-500"></i>
                TOP Reyting (Ballar)
            </h3>
            <div class="space-y-3">
                <?php foreach(($stats['top_students'] ?? []) as $idx => $top): ?>
                    <div class="flex items-center gap-3 p-3 rounded-2xl border border-slate-50 bg-slate-50/50 hover:bg-white hover:shadow-md transition-all group">
                        <div class="h-10 w-10 shrink-0 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center font-bold <?= $idx < 3 ? 'text-orange-500' : 'text-slate-400'; ?>">
                            <?= $idx + 1; ?>
                        </div>
                        <div class="flex-1 min-w-0 text-xs">
                            <p class="text-sm font-bold text-slate-800 truncate group-hover:text-emerald-600 transition-colors"><?= htmlspecialchars($top['fio']); ?></p>
                            <p class="text-[10px] text-slate-400 mt-1 uppercase"><?= (int)$top['total_results']; ?> ta ishtirok</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-black text-slate-800"><?= (int)$top['points']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    // Payment Types Chart
    const ptCtx = document.getElementById('paymentTypeChart');
    if (ptCtx) {
        const ptData = <?= json_encode($stats['payments_by_type'] ?? []); ?>;
        new Chart(ptCtx, {
            type: 'doughnut',
            data: {
                labels: ptData.map(d => d.name),
                datasets: [{
                    data: ptData.map(d => d.total),
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '75%'
            }
        });
    }

    // Direction Chart
    const dirCtx = document.getElementById('directionChart');
    if (dirCtx) {
        const dirData = <?= json_encode($stats['direction_distribution'] ?? []); ?>;
        new Chart(dirCtx, {
            type: 'bar',
            data: {
                labels: dirData.map(d => d.name),
                datasets: [{
                    label: 'Talabalar',
                    data: dirData.map(d => d.cnt),
                    backgroundColor: '#3b82f6',
                    borderRadius: 8,
                    maxBarThickness: 35
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
    }
})();
</script>
