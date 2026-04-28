<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/data_provider.php';

$db = (new Database())->connect();
ensure_system_schema($db);
ensure_mentor_module_schema($db);

$currentPage = $_GET['page'] ?? 'dashboard';
$allowedPages = [
    'dashboard',
    'students',
    'residents',
    'course_students',
    'rooms',
    'courses',
    'mentors',
    'competitions',
    'competition_detail',
    'schedule',
    'statistics',
    'teams',
    'directions',
    'statuses',
];
if ($currentPage === 'settings') {
    $currentPage = 'directions';
}
if (!in_array($currentPage, $allowedPages, true)) {
    $currentPage = 'dashboard';
}
require __DIR__ . '/layout.php';

$pageData = load_page_data($db, $currentPage, $_GET);
$pageOptions = load_page_options($db, $currentPage);
?>
<!doctype html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT-Markaz Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.45);
        }
        .skeleton-line {
            background: linear-gradient(90deg, #e2e8f0 25%, #f8fafc 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s ease-in-out infinite;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .table-shell {
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            overflow: auto;
            background: #fff;
        }
        .admin-table {
            width: 100%;
            min-width: 720px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 0.875rem;
        }
        .admin-table thead th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 600;
            text-align: left;
            padding: 0.7rem 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .admin-table tbody td {
            color: #1e293b;
            padding: 0.7rem 0.75rem;
            border-bottom: 1px solid #eef2f7;
            vertical-align: middle;
            word-break: break-word;
        }
        .admin-table tbody tr:hover td {
            background: #f8fafc;
        }
        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }
        .table-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .admin-modal {
            transition: opacity 180ms ease;
        }
        .admin-modal-panel {
            opacity: 0;
            transform: translateY(10px) scale(0.98);
            transition: transform 180ms ease, opacity 180ms ease;
        }
        .admin-modal.flex .admin-modal-panel {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        .form-grid {
            display: grid;
            gap: 0.875rem;
        }
        .form-field {
            display: grid;
            gap: 0.4rem;
        }
        .form-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
        }
        .form-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 0.625rem;
            padding: 0.55rem 0.7rem;
            background: #fff;
            outline: none;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }
        .form-input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.625rem !important;
            min-height: 42px;
            display: flex !important;
            align-items: center !important;
            padding: 0.25rem 0.35rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0f172a;
            line-height: 1.35rem !important;
            padding-left: 0.35rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 0.5rem !important;
        }
        .select2-dropdown {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            overflow: hidden;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .select2-search__field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding: 0.4rem 0.5rem !important;
        }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 antialiased">
<div class="min-h-screen flex">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div id="mobileSidebarOverlay" class="fixed inset-0 bg-slate-900/40 z-30 hidden md:hidden"></div>

    <div id="mainShell" class="flex-1 flex flex-col transition-all duration-200 md:pl-72">
        <?php include __DIR__ . '/partials/header.php'; ?>
        <main class="flex-1 overflow-auto bg-gray-50 p-4 md:p-6">
            <?php include __DIR__ . '/pages/' . $currentPage . '.php'; ?>
        </main>
        <?php include __DIR__ . '/partials/footer.php'; ?>
    </div>
</div>

<script>window.CURRENT_PAGE = '<?= $currentPage; ?>';</script>
<script>
window.PAGE_OPTIONS = <?= json_encode($pageOptions, JSON_UNESCAPED_UNICODE); ?>;
window.PAGE_DATA = <?= json_encode($pageData, JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="../assets/js/app.js"></script>
</body>
</html>
