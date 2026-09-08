<?php
$notifications = $pageData['notifications'] ?? [];
$unreadCount = $pageData['unread_count'] ?? 0;
$currentTab = $pageData['current_tab'] ?? 'all';

function timeAgoUz($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return "Hozirgina";
    if ($diff < 3600) return floor($diff / 60) . " daqiqa oldin";
    if ($diff < 86400) return floor($diff / 3600) . " soat oldin";
    if ($diff < 86400 * 7) return floor($diff / 86400) . " kun oldin";
    return date('d.m.Y H:i', $time);
}
?>

<div class="space-y-6">
    <!-- Header Card (Matching User Mockup) -->
    <div class="bg-white rounded-2xl p-5 md:p-6 shadow-sm border border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 self-start sm:self-auto">
            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Bildirishnomalar</h2>
                <p class="text-xs text-slate-500">Adminlar faoliyati va tizim harakatlari monitoringi</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
            <!-- Yangilash (Reload) Button -->
            <button type="button" onclick="location.reload()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm hover:shadow-emerald-200 transition-all flex items-center gap-2">
                <i class="fa-solid fa-rotate text-xs"></i>
                <span>Yangilash</span>
            </button>

            <!-- Hammasini o'qilgan deb belgilash Button -->
            <button type="button" onclick="markAllNotificationsRead()" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-check-double text-xs text-emerald-600"></i>
                <span>Hammasini o'qilgan deb belgilash</span>
            </button>
        </div>
    </div>

    <!-- Filter Tabs (Matching User Mockup) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <a href="index.php?page=notifications&tab=all" 
           class="px-4 py-2 rounded-full text-xs font-semibold transition-all whitespace-nowrap <?= $currentTab === 'all' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'; ?>">
            Hammasi
        </a>
        <a href="index.php?page=notifications&tab=students" 
           class="px-4 py-2 rounded-full text-xs font-semibold transition-all whitespace-nowrap <?= $currentTab === 'students' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'; ?>">
            Talabalar
        </a>
        <a href="index.php?page=notifications&tab=payments" 
           class="px-4 py-2 rounded-full text-xs font-semibold transition-all whitespace-nowrap <?= $currentTab === 'payments' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'; ?>">
            To'lovlar
        </a>
        <a href="index.php?page=notifications&tab=admins" 
           class="px-4 py-2 rounded-full text-xs font-semibold transition-all whitespace-nowrap <?= $currentTab === 'admins' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'; ?>">
            Adminlar & Kirish
        </a>
        <a href="index.php?page=notifications&tab=other" 
           class="px-4 py-2 rounded-full text-xs font-semibold transition-all whitespace-nowrap <?= $currentTab === 'other' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'; ?>">
            Boshqalar
        </a>
    </div>

    <!-- Notifications List Container -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
        <?php if (empty($notifications)): ?>
            <!-- Empty State (Exact Match to User Mockup) -->
            <div class="py-20 flex flex-col items-center justify-center text-center space-y-3">
                <div class="h-16 w-16 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 text-3xl">
                    <i class="fa-regular fa-bell-slash"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-base">Sizda hozircha bildirishnomalar yo'q</h4>
                    <p class="text-xs text-slate-400 mt-1">Yangi xabarlar kelganda shu yerda ko'rinadi.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php foreach ($notifications as $item): 
                    $isUnread = (int) $item['is_read'] === 0;
                    $action = $item['action_type'] ?? '';
                    $module = $item['module'] ?? '';
                    
                    $iconClass = 'bg-blue-50 text-blue-600';
                    $icon = 'fa-circle-dot';
                    
                    if ($module === 'students') {
                        $iconClass = 'bg-emerald-50 text-emerald-600';
                        $icon = $action === 'student_delete' ? 'fa-user-minus' : 'fa-user-plus';
                    } elseif ($module === 'payments') {
                        $iconClass = 'bg-teal-50 text-teal-600';
                        $icon = 'fa-money-check-dollar';
                    } elseif ($module === 'admins' || $module === 'auth') {
                        $iconClass = 'bg-purple-50 text-purple-600';
                        $icon = 'fa-shield-halved';
                    } elseif ($module === 'competitions') {
                        $iconClass = 'bg-amber-50 text-amber-600';
                        $icon = 'fa-trophy';
                    }
                    
                    $adminName = trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''));
                    if ($adminName === '') $adminName = $item['username'] ?? 'Tizim';
                ?>
                    <div onclick="markSingleNotificationRead(<?= (int) $item['id']; ?>, this)" 
                         data-id="<?= (int) $item['id']; ?>"
                         data-unread="<?= $isUnread ? '1' : '0'; ?>"
                         class="notification-row group cursor-pointer py-3.5 flex items-start justify-between gap-4 p-3 rounded-xl transition-all duration-200 border border-transparent hover:border-slate-200 <?= $isUnread ? 'bg-emerald-50/30 hover:bg-emerald-50/50' : 'hover:bg-slate-50 opacity-90 hover:opacity-100'; ?>"
                         title="<?= $isUnread ? "Bosib o'qilgan deb belgilash" : "O'qilgan bildirishnoma"; ?>">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <div class="h-10 w-10 shrink-0 rounded-xl <?= $iconClass; ?> flex items-center justify-center text-base mt-0.5 shadow-xs">
                                <i class="fa-solid <?= $icon; ?>"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-xs text-slate-800"><?= htmlspecialchars($adminName); ?></span>
                                    <span class="text-[11px] font-mono text-slate-400">(@<?= htmlspecialchars($item['username'] ?? 'admin'); ?>)</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold uppercase tracking-wider"><?= htmlspecialchars($module); ?></span>
                                    <span class="read-status-badge text-[10px] px-1.5 py-0.2 rounded font-medium <?= $isUnread ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400'; ?>">
                                        <?= $isUnread ? "Yangi" : "O'qilgan"; ?>
                                    </span>
                                </div>
                                <p class="text-sm text-slate-700 mt-1 font-medium leading-normal"><?= htmlspecialchars($item['description']); ?></p>
                                <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    <span><?= timeAgoUz($item['created_at']); ?></span>
                                    <span>•</span>
                                    <span><?= date('d.m.Y H:i', strtotime($item['created_at'])); ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="indicator-col shrink-0 flex items-center gap-2 pt-1">
                            <?php if ($isUnread): ?>
                                <span class="unread-dot h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-sm animate-pulse" title="Yangi o'qilmagan xabar"></span>
                            <?php else: ?>
                                <span class="text-slate-300 text-xs"><i class="fa-solid fa-check text-[11px]"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function syncUnreadBadges(newCount) {
    const headerBadge = document.getElementById('headerUnreadBadge');
    const sidebarBadge = document.getElementById('sidebarUnreadBadge');
    
    [headerBadge, sidebarBadge].forEach(b => {
        if (!b) return;
        if (newCount > 0) {
            b.textContent = newCount > 99 ? '99+' : newCount;
            b.style.display = 'flex';
            b.classList.remove('hidden');
            b.classList.add('flex');
        } else {
            b.style.display = 'none';
            b.classList.add('hidden');
            b.classList.remove('flex');
        }
    });
}

async function markSingleNotificationRead(id, rowEl) {
    if (!rowEl || rowEl.getAttribute('data-unread') !== '1') {
        return; // Already read
    }

    try {
        const fd = new FormData();
        fd.append('id', id);

        const res = await fetch('../update/mark_notifications_read.php', {
            method: 'POST',
            body: fd
        });
        const data = await res.json();

        if (data.success) {
            // Update row element state visually
            rowEl.setAttribute('data-unread', '0');
            rowEl.classList.remove('bg-emerald-50/30', 'hover:bg-emerald-50/50');
            rowEl.classList.add('hover:bg-slate-50', 'opacity-90');
            rowEl.setAttribute('title', "O'qilgan bildirishnoma");

            // Update badge text
            const statusBadge = rowEl.querySelector('.read-status-badge');
            if (statusBadge) {
                statusBadge.className = 'read-status-badge text-[10px] px-1.5 py-0.2 rounded font-medium bg-slate-100 text-slate-400';
                statusBadge.textContent = "O'qilgan";
            }

            // Replace dot with checkmark
            const indCol = rowEl.querySelector('.indicator-col');
            if (indCol) {
                indCol.innerHTML = '<span class="text-emerald-500 text-xs transition-all duration-300 scale-110"><i class="fa-solid fa-check text-[11px]"></i></span>';
            }

            if (typeof data.data?.unread_count !== 'undefined') {
                syncUnreadBadges(data.data.unread_count);
            }
        }
    } catch (err) {
        console.error('Bildirishnomani belgilashda xatolik:', err);
    }
}

async function markAllNotificationsRead() {
    try {
        const res = await fetch('../update/mark_notifications_read.php', { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            // Update all rows on screen immediately
            document.querySelectorAll('.notification-row').forEach(rowEl => {
                rowEl.setAttribute('data-unread', '0');
                rowEl.classList.remove('bg-emerald-50/30', 'hover:bg-emerald-50/50');
                rowEl.classList.add('hover:bg-slate-50', 'opacity-90');
                rowEl.setAttribute('title', "O'qilgan bildirishnoma");

                const statusBadge = rowEl.querySelector('.read-status-badge');
                if (statusBadge) {
                    statusBadge.className = 'read-status-badge text-[10px] px-1.5 py-0.2 rounded font-medium bg-slate-100 text-slate-400';
                    statusBadge.textContent = "O'qilgan";
                }

                const indCol = rowEl.querySelector('.indicator-col');
                if (indCol) {
                    indCol.innerHTML = '<span class="text-slate-300 text-xs"><i class="fa-solid fa-check text-[11px]"></i></span>';
                }
            });

            syncUnreadBadges(0);

            Swal.fire({
                icon: 'success',
                title: 'Bajarildi',
                text: 'Barcha bildirishnomalar o\'qilgan deb belgilandi.',
                timer: 1200,
                showConfirmButton: false
            });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Xatolik', text: 'Server bilan aloqa uzildi.' });
    }
}
</script>
