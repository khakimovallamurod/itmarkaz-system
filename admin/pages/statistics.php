<div class="p-4 space-y-4">
    <div class="bg-white rounded-xl p-4 shadow">
        <h2 class="font-semibold">Admin Analytics Panel</h2>
        <p class="text-xs text-slate-500 mt-1">Jami ko'rsatkichlar, g'oliblar va top talabalar</p>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Jami talabalar</p><h2 id="analyticsStudents" class="mt-2 text-3xl font-semibold text-slate-900">0</h2></article>
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Rezidentlar</p><h2 id="analyticsResidents" class="mt-2 text-3xl font-semibold text-slate-900">0</h2></article>
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Kurs o'quvchilar</p><h2 id="analyticsCourseStudents" class="mt-2 text-3xl font-semibold text-slate-900">0</h2></article>
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Mentorlar</p><h2 id="analyticsMentors" class="mt-2 text-3xl font-semibold text-slate-900">0</h2></article>
        <article class="glass-card rounded-xl shadow-sm p-5"><p class="text-sm text-slate-500">Tanlovlar</p><h2 id="analyticsCompetitions" class="mt-2 text-3xl font-semibold text-slate-900">0</h2></article>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        <section class="bg-white rounded-xl p-4 shadow">
            <h3 class="font-semibold mb-3">Tanlov natijalari</h3>
            <canvas id="analyticsChart" height="130"></canvas>
        </section>
        <section class="bg-white rounded-xl p-4 shadow">
            <h3 class="font-semibold mb-3">G'oliblar (1-o'rin)</h3>
            <div id="winnerList" class="space-y-2"></div>
        </section>
    </div>

    <section class="bg-white rounded-xl p-4 shadow">
        <h3 class="font-semibold mb-3">TOP Talabalar</h3>
        <div id="topStudentsList" class="grid md:grid-cols-2 xl:grid-cols-3 gap-3"></div>
    </section>
</div>
