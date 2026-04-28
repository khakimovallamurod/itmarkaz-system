<?php $stats = $pageData['stats'] ?? []; ?>
<div class="space-y-5">
    <section class="grid sm:grid-cols-2 xl:grid-cols-5 gap-4" id="dashboardStatsCards">
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Jami talabalar</p><h2 class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($stats['students'] ?? 0); ?></h2></article>
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Rezidentlar</p><h2 class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($stats['residents'] ?? 0); ?></h2></article>
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Kurs o'quvchilar</p><h2 class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($stats['course_students'] ?? 0); ?></h2></article>
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Mentorlar</p><h2 class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($stats['mentors'] ?? 0); ?></h2></article>
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Tanlovlar</p><h2 class="mt-2 text-3xl font-semibold text-slate-900"><?= (int) ($stats['competitions'] ?? 0); ?></h2></article>
    </section>

    <section class="grid lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5"><h3 class="font-semibold text-slate-900 mb-4">Natijalar Overview</h3><canvas id="dashboardChart" height="110"></canvas></div>
        <div class="bg-white rounded-xl shadow-sm p-5"><h3 class="font-semibold text-slate-900 mb-4">Recent Activity</h3><ul class="space-y-3 text-sm text-slate-600"><?php foreach(($stats['recent_activity'] ?? []) as $act): ?><li class="p-3 rounded-lg bg-slate-50"><p class="text-sm text-slate-700"><?= htmlspecialchars($act['title']); ?></p><p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars((string)$act['created_at']); ?></p></li><?php endforeach; ?><?php if (empty($stats['recent_activity'])): ?><li class="p-3 rounded-lg bg-slate-50 text-slate-500">Faollik topilmadi</li><?php endif; ?></ul></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-5"><h3 class="font-semibold text-slate-900 mb-4">Upcoming Tanlovlar</h3><div class="grid md:grid-cols-2 xl:grid-cols-3 gap-3"><?php foreach(($stats['upcoming_competitions'] ?? []) as $c): ?><article class="rounded-xl border border-slate-200 p-3 bg-slate-50"><p class="font-medium text-slate-900"><?= htmlspecialchars($c['name']); ?></p><p class="text-sm text-slate-600 mt-1"><?= htmlspecialchars($c['competition_date']); ?></p><p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($c['location'] ?: 'Manzil kiritilmagan'); ?></p></article><?php endforeach; ?><?php if (empty($stats['upcoming_competitions'])): ?><p class="text-sm text-slate-500">Yaqin tanlovlar mavjud emas.</p><?php endif; ?></div></section>
</div>
