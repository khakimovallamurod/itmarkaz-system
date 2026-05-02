<?php
$items = $pageData['items'] ?? [];
$totalSum = $pageData['total_sum'] ?? 0;
$pagination = $pageData['pagination'] ?? ['page' => 1, 'pages' => 1, 'total' => 0, 'offset' => 0];
$filters = $pageData['filters'] ?? [];
$projects = $pageOptions['all_projects'] ?? [];
$paymentTypes = $pageOptions['payment_types'] ?? [];

function sortIcon($field, $filters) {
    $currentSort = $filters['sort_by'] ?? '';
    $currentOrder = $filters['sort_order'] ?? '';
    if ($currentSort !== $field) return '<i class="fa-solid fa-sort text-slate-300 ml-1 text-[10px]"></i>';
    return $currentOrder === 'ASC' 
        ? '<i class="fa-solid fa-sort-up text-emerald-600 ml-1"></i>' 
        : '<i class="fa-solid fa-sort-down text-emerald-600 ml-1"></i>';
}
?>

<div class="p-4 space-y-4">
    <!-- Header with Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="h-12 w-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <i class="fa-solid fa-money-bill-trend-up text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Umumiy summa</p>
                <p class="text-2xl font-bold text-slate-800"><?= number_format($totalSum, 0, '.', ' '); ?> <span class="text-sm font-normal text-slate-500">so'm</span></p>
            </div>
        </div>
        <!-- Spacer or other stats if needed -->
        <div class="md:col-span-2 flex items-center justify-end">
             <button type="button" onclick="openPaymentModal()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm transition-all flex items-center gap-2 font-medium">
                <i class="fa-solid fa-plus"></i>
                To'lov qo'shish
            </button>
        </div>
    </div>

    <!-- Filters -->
    <form method="get" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col md:flex-row gap-3 items-center">
        <input type="hidden" name="page" value="payments">
        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($filters['sort_by'] ?? ''); ?>">
        <input type="hidden" name="sort_order" value="<?= htmlspecialchars($filters['sort_order'] ?? ''); ?>">
        
        <div class="relative w-full md:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input name="search" value="<?= htmlspecialchars($filters['search'] ?? ''); ?>" 
                   class="w-full pl-10 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" 
                   placeholder="FIO yoki loyiha bo'yicha...">
        </div>

        <select name="project_id" class="w-full md:w-60 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none transition-all">
            <option value="">Barcha loyihalar</option>
            <?php foreach ($projects as $project): ?>
                <option value="<?= (int) $project['id']; ?>" <?= ((int) ($filters['project_id'] ?? 0) === (int) $project['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($project['project_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="payment_type_id" class="w-full md:w-60 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-emerald-500 outline-none transition-all">
            <option value="">To'lov turi</option>
            <?php foreach ($paymentTypes as $type): ?>
                <option value="<?= (int) $type['id']; ?>" <?= ((int) ($filters['payment_type_id'] ?? 0) === (int) $type['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($type['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="flex items-center gap-2">
            <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? ''); ?>" 
                   class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 outline-none">
            <span class="text-slate-400">-</span>
            <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? ''); ?>" 
                   class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 outline-none">
        </div>

        <button class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm hover:bg-slate-900 transition-all">Filter</button>
        <a href="?page=payments" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg text-sm hover:bg-slate-50 transition-all">Tozalash</a>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider border-bottom border-slate-100 w-16">#</th>
                        <th data-sort="student_fio" class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider border-bottom border-slate-100">
                            Talaba FIO <?= sortIcon('student_fio', $filters); ?>
                        </th>
                        <th data-sort="pr.project_name" class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider border-bottom border-slate-100">
                            Loyiha <?= sortIcon('pr.project_name', $filters); ?>
                        </th>
                        <th data-sort="p.amount" class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider border-bottom border-slate-100">
                            Summa <?= sortIcon('p.amount', $filters); ?>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider border-bottom border-slate-100">To'lov turi</th>
                        <th data-sort="p.created_at" class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider border-bottom border-slate-100">
                            Sana <?= sortIcon('p.created_at', $filters); ?>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider border-bottom border-slate-100 text-right">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!$items): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400 italic">Ma'lumot topilmadi</td>
                        </tr>
                    <?php endif; ?>
                    <?php 
                    $totalCount = $pagination['total'] ?? 0;
                    $offset = $pagination['offset'] ?? 0;
                    foreach ($items as $index => $item): 
                        $rowNum = $totalCount - $offset - $index;
                        $itemJson = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4 text-slate-400 font-mono text-xs"><?= $rowNum; ?></td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-700"><?= htmlspecialchars($item['student_fio']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-sm">
                                <?= htmlspecialchars($item['project_name']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-emerald-600"><?= number_format($item['amount'], 0, '.', ' '); ?></span>
                                <span class="text-xs text-slate-400">so'm</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">
                                    <?= htmlspecialchars($item['type_name']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-sm">
                                <?= date('d.m.Y H:i', strtotime($item['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button" class="js-payment-edit h-8 w-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors" 
                                            data-item="<?= $itemJson; ?>" title="Tahrirlash">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button type="button" class="js-payment-delete h-8 w-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" 
                                            data-id="<?= (int) $item['id']; ?>" title="O'chirish">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <div class="p-4 border-t border-slate-100 flex items-center justify-center gap-1">
                <?php for ($i = 1; $i <= (int) $pagination['pages']; $i++): ?>
                    <a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['p' => $i]))); ?>" 
                       class="w-9 h-9 flex items-center justify-center rounded-lg border <?= $i === (int) $pagination['page'] ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'border-slate-200 text-slate-600 hover:bg-slate-50' ?> text-sm transition-all">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Payment Modal -->
<div id="paymentModal" class="admin-modal hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 items-center justify-center p-4">
    <div class="admin-modal-panel bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="paymentModalTitle" class="text-lg font-bold text-slate-800">To'lov kiritish</h3>
            <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="paymentForm" class="p-6 space-y-4">
            <input type="hidden" name="id" id="paymentId">
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-slate-700">Loyihani tanlang</label>
                <select name="project_id" id="paymentProjectSelect" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" required>
                    <option value="">Loyiha tanlang</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= (int) $project['id']; ?>"><?= htmlspecialchars($project['project_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-slate-700">Talabani tanlang</label>
                <select name="student_id" id="paymentStudentSelect" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" required disabled>
                    <option value="">Loyihani tanlang...</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700">Summa (so'm)</label>
                    <input type="text" name="amount" id="paymentAmountInput" data-number-format placeholder="Masalan: 500 000" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" required>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700">To'lov turi</label>
                    <select name="payment_type_id" id="paymentTypeSelect" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all" required>
                        <?php foreach ($paymentTypes as $type): ?>
                            <option value="<?= (int) $type['id']; ?>"><?= htmlspecialchars($type['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <button type="button" onclick="closePaymentModal()" class="px-6 py-2.5 border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition-all font-medium">
                    Bekor qilish
                </button>
                <button type="submit" id="paymentSubmitBtn" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-md shadow-emerald-600/20 transition-all font-medium">
                    Saqlash
                </button>
            </div>
        </form>
    </div>
</div>
