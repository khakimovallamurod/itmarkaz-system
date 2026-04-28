<div class="space-y-5">
    <section class="grid sm:grid-cols-2 xl:grid-cols-5 gap-4" id="dashboardStatsCards">
        <article class="glass-card rounded-xl shadow-sm p-5 transition-all duration-200 hover:scale-[1.02]">
            <p class="text-sm text-slate-500">Jami talabalar</p>
            <h2 id="statStudents" class="mt-2 text-3xl font-semibold text-slate-900">0</h2>
        </article>
        <article class="glass-card rounded-xl shadow-sm p-5 transition-all duration-200 hover:scale-[1.02]">
            <p class="text-sm text-slate-500">Rezidentlar</p>
            <h2 id="statResidents" class="mt-2 text-3xl font-semibold text-slate-900">0</h2>
        </article>
        <article class="glass-card rounded-xl shadow-sm p-5 transition-all duration-200 hover:scale-[1.02]">
            <p class="text-sm text-slate-500">Kurs o'quvchilar</p>
            <h2 id="statCourseStudents" class="mt-2 text-3xl font-semibold text-slate-900">0</h2>
        </article>
        <article class="glass-card rounded-xl shadow-sm p-5 transition-all duration-200 hover:scale-[1.02]">
            <p class="text-sm text-slate-500">Mentorlar</p>
            <h2 id="statMentors" class="mt-2 text-3xl font-semibold text-slate-900">0</h2>
        </article>
        <article class="glass-card rounded-xl shadow-sm p-5 transition-all duration-200 hover:scale-[1.02]">
            <p class="text-sm text-slate-500">Tanlovlar</p>
            <h2 id="statCompetitions" class="mt-2 text-3xl font-semibold text-slate-900">0</h2>
        </article>
    </section>

    <section class="grid lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-900 mb-4">Natijalar Overview</h3>
            <canvas id="dashboardChart" height="110"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-900 mb-4">Recent Activity</h3>
            <ul id="recentActivity" class="space-y-3 text-sm text-slate-600"></ul>
        </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold text-slate-900 mb-4">Upcoming Tanlovlar</h3>
        <div id="upcomingCompetitions" class="grid md:grid-cols-2 xl:grid-cols-3 gap-3"></div>
    </section>
</div>
