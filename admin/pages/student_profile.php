<?php
$student = $pageData['student'] ?? null;
$statuses = $pageData['statuses'] ?? [];
$resident = $pageData['resident'] ?? null;
$courses = $pageData['courses'] ?? [];
$mentorCourses = $pageData['mentor_courses'] ?? [];
$projects = $pageData['projects'] ?? [];
$teams = $pageData['teams'] ?? [];
$payments = $pageData['payments'] ?? [];
$totalPaid = $pageData['total_paid'] ?? 0;
$competitions = $pageData['competitions'] ?? [];
$adminAccount = $pageData['admin_account'] ?? null;

$currentYear = (int) date('Y');
$courseLabel = static function (int $entryYear) use ($currentYear): string {
    if ($entryYear < 1900) return '-';
    $elapsed = $currentYear - $entryYear;
    if ($elapsed < 0) return 'Hali boshlanmagan';
    if ($elapsed > 4) return 'Bitirgan';
    return (($elapsed === 0 ? 1 : $elapsed) . '-kurs');
};

if (!$student): ?>
    <div class="bg-white rounded-2xl p-10 border border-slate-100 shadow-sm text-center max-w-lg mx-auto my-12 space-y-4">
        <div class="h-16 w-16 mx-auto rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-user-xmark"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800">Talaba topilmadi</h3>
        <p class="text-xs text-slate-500">Bunday ID bilan talaba ma'lumotlar bazasida mavjud emas yoki o'chirilgan bo'lishi mumkin.</p>
        <div class="pt-2">
            <a href="index.php?page=students" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Talabalar ro'yxatiga qaytish</span>
            </a>
        </div>
    </div>
<?php return; endif; 

$fioParts = explode(' ', trim($student['fio']));
$initials = mb_substr($fioParts[0] ?? '', 0, 1, 'UTF-8') . mb_substr($fioParts[1] ?? '', 0, 1, 'UTF-8');
$initials = mb_strtoupper($initials, 'UTF-8');
if (!$initials) $initials = 'TL';
?>

<div class="space-y-6">
    <!-- Top Action Bar & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="index.php?page=students" 
               class="h-9 px-3.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Talabalar ro'yxatiga qaytish</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs font-semibold text-slate-500 truncate max-w-xs">
                <?= htmlspecialchars($student['fio']); ?>
            </span>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="h-9 px-3.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all shadow-xs" title="Chop etish">
                <i class="fa-solid fa-print text-slate-400"></i>
                <span>Chop etish</span>
            </button>
        </div>
    </div>

    <!-- Hero Card: Profile Header (Clean, Modern, No awkward dark overlap) -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="h-20 w-20 shrink-0 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-2xl flex items-center justify-center shadow-md shadow-emerald-500/20 ring-4 ring-emerald-50">
                    <?= htmlspecialchars($initials); ?>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-2xl font-bold text-slate-900"><?= htmlspecialchars($student['fio']); ?></h2>
                        <?php if ($resident): ?>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center gap-1 border border-emerald-200">
                                <i class="fa-solid fa-hotel text-[10px]"></i> Rezident
                            </span>
                        <?php endif; ?>
                        <?php if ($adminAccount): ?>
                            <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 text-xs font-bold flex items-center gap-1 border border-purple-200">
                                <i class="fa-solid fa-shield-halved text-[10px]"></i> Admin (@<?= htmlspecialchars($adminAccount['username']); ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-2 flex-wrap">
                        <span><i class="fa-solid fa-graduation-cap text-emerald-600 mr-1"></i><?= htmlspecialchars($student['yonalish_name'] ?? 'Yo\'nalish belgilanmagan'); ?></span>
                        <span class="text-slate-300">•</span>
                        <span>Guruh: <strong class="text-slate-800"><?= htmlspecialchars($student['guruh']); ?></strong></span>
                        <span class="text-slate-300">•</span>
                        <span><strong class="text-emerald-700 font-semibold"><?= htmlspecialchars($courseLabel((int) $student['kirgan_yili'])); ?></strong> (<?= (int) $student['kirgan_yili']; ?>-yil)</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 flex-wrap self-start md:self-center">
                <?php foreach ($statuses as $st): ?>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200/60">
                        <?= htmlspecialchars($st['name']); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] text-slate-400 font-medium">Telefon raqam</p>
                    <a href="tel:<?= htmlspecialchars($student['telefon']); ?>" class="text-xs font-bold text-slate-800 hover:text-emerald-600 transition-colors truncate block">
                        <?= htmlspecialchars($student['telefon']); ?>
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                <div class="h-10 w-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-brands fa-telegram"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] text-slate-400 font-medium">Telegram Chat ID</p>
                    <p class="text-xs font-bold text-slate-800 font-mono truncate">
                        <?= !empty($student['telegram_chat_id']) ? htmlspecialchars($student['telegram_chat_id']) : '<span class="text-slate-400 font-normal">Kiritilmagan</span>'; ?>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] text-slate-400 font-medium">Ro'yxatdan o'tgan sana</p>
                    <p class="text-xs font-bold text-slate-800">
                        <?= !empty($student['created_at']) ? date('d.m.Y H:i', strtotime($student['created_at'])) : '-'; ?>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] text-slate-400 font-medium">Jami to'lovlar summasi</p>
                    <p class="text-xs font-bold text-emerald-700">
                        <?= number_format($totalPaid, 0, '', ' '); ?> so'm
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stat KPI Boxes -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-laptop-code"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Rezidentlik</p>
                <p class="text-sm font-bold <?= $resident ? 'text-emerald-700' : 'text-slate-500'; ?> mt-0.5">
                    <?= $resident ? "Faol (Xona: {$resident['room_number']})" : "Rezident emas"; ?>
                </p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-book-open-reader"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Kurslar</p>
                <p class="text-sm font-bold text-slate-800 mt-0.5">
                    <?= count($courses); ?> ta kursga yozilgan
                </p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-diagram-project"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Loyihalar & Jamoa</p>
                <p class="text-sm font-bold text-slate-800 mt-0.5">
                    <?= count($projects); ?> loyiha / <?= count($teams); ?> jamoa
                </p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-trophy"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Musobaqalar</p>
                <p class="text-sm font-bold text-slate-800 mt-0.5">
                    <?= count($competitions); ?> ta tanlov
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Card: Rezidentlik Holati -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-hotel"></i>
                    </span>
                    <h3 class="font-bold text-slate-900 text-sm">Rezidentlik Holati</h3>
                </div>
                <?php if ($resident): ?>
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">Faol Rezident</span>
                <?php else: ?>
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 font-semibold">Rezident emas</span>
                <?php endif; ?>
            </div>

            <?php if ($resident): ?>
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <p class="text-[11px] text-slate-400 font-medium">Biriktirilgan Xona</p>
                        <p class="text-sm font-bold text-slate-800 mt-0.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-door-open text-emerald-600 text-xs"></i>
                            <?= htmlspecialchars($resident['room_number'] ?? 'Belgilanmagan'); ?>-xona
                        </p>
                        <p class="text-[10px] text-slate-400 mt-1">Sig'im: <?= (int) ($resident['capacity'] ?? 0); ?> o'rin</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <p class="text-[11px] text-slate-400 font-medium">Kompyuter Raqami</p>
                        <p class="text-sm font-bold text-slate-800 mt-0.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-desktop text-blue-600 text-xs"></i>
                            <?= !empty($resident['computer_number']) ? htmlspecialchars($resident['computer_number']) : 'Umumiy'; ?>
                        </p>
                        <p class="text-[10px] text-slate-400 mt-1">Xonadagi kompyuterlar: <?= (int) ($resident['computers_count'] ?? 0); ?> ta</p>
                    </div>
                </div>

                <div class="text-[11px] text-slate-500 bg-emerald-50/50 p-2.5 rounded-xl border border-emerald-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                    <span>Ushbu talaba IT-Markaz rezidenti hisoblanadi va unga doimiy ish o'rni ajratilgan.</span>
                </div>
            <?php else: ?>
                <div class="py-6 text-center text-slate-400 space-y-1">
                    <i class="fa-solid fa-id-badge text-3xl text-slate-300 mb-1"></i>
                    <p class="text-xs font-medium text-slate-600">Hozircha rezidentlik biriktirilmagan</p>
                    <p class="text-[11px]">Talaba oddiy rejimda o'qimoqda yoki kurslarga qatnashmoqda.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card: Yozilgan Kurslar -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-book-open"></i>
                    </span>
                    <h3 class="font-bold text-slate-900 text-sm">O'quv Kurslari (<?= count($courses); ?>)</h3>
                </div>
            </div>

            <?php if (!empty($courses)): ?>
                <div class="space-y-2.5">
                    <?php foreach ($courses as $c): 
                        $statusText = ($c['status'] ?? 'active') === 'completed' ? 'Yakunlagan' : 'Faol o\'qimoqda';
                        $statusClass = ($c['status'] ?? 'active') === 'completed' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-700';
                    ?>
                        <div class="p-3 rounded-xl border border-slate-100 hover:border-slate-200 transition-all bg-slate-50/60 flex items-start justify-between gap-3">
                            <div>
                                <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-graduation-cap text-blue-500 text-xs"></i>
                                    <?= htmlspecialchars($c['course_name']); ?>
                                </h4>
                                <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-1 flex-wrap">
                                    <?php if (!empty($c['room_number'])): ?>
                                        <span><i class="fa-regular fa-door-closed mr-0.5"></i><?= htmlspecialchars($c['room_number']); ?>-xona</span>
                                        <span>•</span>
                                    <?php endif; ?>
                                    <?php if (!empty($c['time'])): ?>
                                        <span><i class="fa-regular fa-clock mr-0.5"></i><?= htmlspecialchars($c['time']); ?></span>
                                        <span>•</span>
                                    <?php endif; ?>
                                    <?php if (!empty($c['duration'])): ?>
                                        <span><?= htmlspecialchars($c['duration']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold <?= $statusClass; ?>">
                                <?= $statusText; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="py-6 text-center text-slate-400 space-y-1">
                    <i class="fa-solid fa-book-open-reader text-3xl text-slate-300 mb-1"></i>
                    <p class="text-xs font-medium text-slate-600">Hozirda kurslarga a'zo emas</p>
                    <p class="text-[11px]">Talaba hech qaysi o'quv kursiga yozilmagan.</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($mentorCourses)): ?>
                <div class="pt-3 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-800 mb-2 flex items-center gap-1.5 text-amber-600">
                        <i class="fa-solid fa-chalkboard-teacher"></i>
                        <span>Mentorlik qilayotgan kurslari:</span>
                    </p>
                    <div class="space-y-1.5">
                        <?php foreach ($mentorCourses as $mc): ?>
                            <div class="px-3 py-2 rounded-lg bg-amber-50/60 border border-amber-100 text-xs font-semibold text-amber-900 flex items-center justify-between">
                                <span><?= htmlspecialchars($mc['course_name']); ?></span>
                                <span class="text-[11px] text-amber-700 font-normal"><?= htmlspecialchars($mc['time'] ?? ''); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card: Loyihalar & Upwork Jamoalar -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="h-8 w-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-diagram-project"></i>
                    </span>
                    <h3 class="font-bold text-slate-900 text-sm">Loyihalar va Upwork Jamoalar</h3>
                </div>
            </div>

            <!-- Projects Sub-section -->
            <div>
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Qatnashayotgan Loyihalari (<?= count($projects); ?>)</h4>
                <?php if (!empty($projects)): ?>
                    <div class="space-y-2">
                        <?php foreach ($projects as $pr): 
                            $statusMap = [
                                'boshlanish' => ['Boshlanish', 'bg-blue-100 text-blue-700'],
                                'qurish' => ['Qurish', 'bg-amber-100 text-amber-700'],
                                'testlash' => ['Testlash', 'bg-purple-100 text-purple-700'],
                                'tugallash' => ['Tugallash', 'bg-emerald-100 text-emerald-700'],
                            ];
                            $stInfo = $statusMap[$pr['project_status']] ?? [ucfirst($pr['project_status']), 'bg-slate-100 text-slate-600'];
                        ?>
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-code-branch text-purple-500 text-xs"></i>
                                    <?= htmlspecialchars($pr['project_name']); ?>
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold <?= $stInfo[1]; ?>">
                                    <?= $stInfo[0]; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-slate-400 py-1">Loyihalarga a'zo emas.</p>
                <?php endif; ?>
            </div>

            <!-- Upwork Teams Sub-section -->
            <div class="pt-3 border-t border-slate-100">
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Upwork Jamoalari (<?= count($teams); ?>)</h4>
                <?php if (!empty($teams)): ?>
                    <div class="space-y-2">
                        <?php foreach ($teams as $tm): 
                            $lvlMap = [
                                'junior' => ['Junior', 'bg-sky-100 text-sky-700'],
                                'middle' => ['Middle', 'bg-indigo-100 text-indigo-700'],
                                'senior' => ['Senior', 'bg-emerald-100 text-emerald-700'],
                            ];
                            $lvlInfo = $lvlMap[$tm['team_level']] ?? [ucfirst($tm['team_level']), 'bg-slate-100 text-slate-600'];
                        ?>
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-users text-indigo-500 text-xs"></i>
                                    <?= htmlspecialchars($tm['team_name']); ?>
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold <?= $lvlInfo[1]; ?>">
                                    <?= $lvlInfo[0]; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-slate-400 py-1">Jamoalarga a'zo emas.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card: Musobaqalar va Tanlovlar -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-trophy"></i>
                    </span>
                    <h3 class="font-bold text-slate-900 text-sm">Tanlovlar va Natijalar (<?= count($competitions); ?>)</h3>
                </div>
            </div>

            <?php if (!empty($competitions)): ?>
                <div class="space-y-2.5">
                    <?php foreach ($competitions as $cp): ?>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3">
                            <div>
                                <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-award text-amber-500 text-xs"></i>
                                    <?= htmlspecialchars($cp['competition_name']); ?>
                                </h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    <?= !empty($cp['competition_date']) ? date('d.m.Y', strtotime($cp['competition_date'])) : ''; ?>
                                    <?= !empty($cp['location']) ? ' • ' . htmlspecialchars($cp['location']) : ''; ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <?php if (!empty($cp['position'])): ?>
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-xs block">
                                        <?= (int) $cp['position']; ?>-o'rin
                                    </span>
                                    <?php if (!empty($cp['cash_amount']) && (float)$cp['cash_amount'] > 0): ?>
                                        <span class="text-[10px] text-emerald-600 font-bold block mt-0.5">
                                            +<?= number_format((float)$cp['cash_amount'], 0, '', ' '); ?> so'm
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-semibold">
                                        Ishtirokchi
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="py-6 text-center text-slate-400 space-y-1">
                    <i class="fa-solid fa-award text-3xl text-slate-300 mb-1"></i>
                    <p class="text-xs font-medium text-slate-600">Tanlovlarda ishtirok etmagan</p>
                    <p class="text-[11px]">Talaba hali biron bir musobaqada qatnashmagan.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Card: To'lovlar Tarixi (Full Width) -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <span class="h-8 w-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-receipt"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">To'lovlar Tarixi</h3>
                    <p class="text-[11px] text-slate-400">Ushbu talaba tomonidan amalga oshirilgan to'lovlar</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500">Jami to'langan:</span>
                <span class="text-sm font-bold text-emerald-600 ml-1"><?= number_format($totalPaid, 0, '', ' '); ?> so'm</span>
            </div>
        </div>

        <?php if (!empty($payments)): ?>
            <div class="table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Summa</th>
                            <th>To'lov turi</th>
                            <th>Bog'langan Loyiha</th>
                            <th>To'lov vaqti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $idx => $p): ?>
                            <tr>
                                <td class="font-mono text-xs text-slate-400"><?= $idx + 1; ?></td>
                                <td class="font-bold text-emerald-700 text-xs">
                                    <?= number_format((float) $p['amount'], 0, '', ' '); ?> so'm
                                </td>
                                <td>
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px] font-semibold">
                                        <?= htmlspecialchars($p['payment_type_name'] ?? 'To\'lov'); ?>
                                    </span>
                                </td>
                                <td class="text-slate-700 text-xs">
                                    <?= !empty($p['project_name']) ? htmlspecialchars($p['project_name']) : '<span class="text-slate-400">-</span>'; ?>
                                </td>
                                <td class="text-slate-500 text-xs">
                                    <?= !empty($p['created_at']) ? date('d.m.Y H:i', strtotime($p['created_at'])) : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="py-8 text-center text-slate-400 space-y-1">
                <i class="fa-solid fa-money-check-dollar text-3xl text-slate-300 mb-1"></i>
                <p class="text-xs font-medium text-slate-600">To'lovlar mavjud emas</p>
                <p class="text-[11px]">Talaba hisobida hali to'lovlar qayd etilmagan.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
