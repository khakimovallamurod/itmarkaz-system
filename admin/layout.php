<?php
$currentPage = $currentPage ?? 'dashboard';

$pageMeta = [
    'dashboard' => ['title' => 'Dashboard', 'subtitle' => 'Tizim ko\'rsatkichlari va so\'nggi faolliklar'],
    'residents' => ['title' => 'Rezidentlar', 'subtitle' => 'Faol rezidentlar ro\'yxati'],
    'students' => ['title' => 'Talabalar', 'subtitle' => 'Talabalar bazasi va statuslari'],
    'course_students' => ['title' => 'Kurs o\'quvchilar', 'subtitle' => 'Kurs bo\'yicha o\'quvchilar monitoringi'],
    'rooms' => ['title' => 'Xonalar', 'subtitle' => 'Xona va kompyuter sig\'imini boshqarish'],
    'courses' => ['title' => 'Kurslar', 'subtitle' => 'Kurslar jadvali va davomiyligi'],
    'mentors' => ['title' => 'Mentorlar', 'subtitle' => 'Rezident talabalarni mentor sifatida kurslarga biriktirish'],
    'competitions' => ['title' => 'Tanlovlar', 'subtitle' => 'Tanlovlar ro\'yxati va bildirishnomalar'],
    'competition_detail' => ['title' => 'Tanlov Tafsiloti', 'subtitle' => 'Ishtirokchilar, xabar yuborish va natijalar'],
    'schedule' => ['title' => 'Ish Jadvali', 'subtitle' => 'Kunlik va haftalik reja boshqaruvi'],
    'statistics' => ['title' => 'Statistika', 'subtitle' => 'Asosiy ko\'rsatkichlar va top natijalar'],
    'teams' => ['title' => 'Upwork Jamoa', 'subtitle' => 'Jamoalarni yaratish va a\'zolarni boshqarish'],
    'directions' => ['title' => 'Sozlamalar', 'subtitle' => 'Yo\'nalishlarni boshqarish'],
    'statuses' => ['title' => 'Sozlamalar', 'subtitle' => 'Statuslarni boshqarish'],
];

$currentMeta = $pageMeta[$currentPage] ?? $pageMeta['dashboard'];
$pageTitle = $currentMeta['title'];
$pageSubtitle = $currentMeta['subtitle'];
