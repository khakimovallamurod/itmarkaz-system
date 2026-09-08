<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}
if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    header('Location: ../admin/index.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/data_provider.php';

$db = (new Database())->connect();
ensure_system_schema($db);
ensure_mentor_module_schema($db);

$decodeEntities = static function ($value) use (&$decodeEntities) {
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = $decodeEntities($v);
        }
        return $value;
    }
    if (is_string($value)) {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return $value;
};

$currentPage = $_GET['page'] ?? 'dashboard';
$allowedPages = [
    'dashboard',
    'admins',
    'notifications',
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
    'projects',
    'payments',
    'directions',
    'statuses',
    'settings',
    'student_profile',
];

if (!in_array($currentPage, $allowedPages, true)) {
    $currentPage = 'dashboard';
}

require __DIR__ . '/layout.php';

$pageData = load_superadmin_page_data($db, $currentPage, $_GET);
$pageOptions = load_page_options($db, $currentPage);
$pageData = $decodeEntities($pageData);
$pageOptions = $decodeEntities($pageOptions);
?>
<!doctype html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin | IT-Markaz</title>
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
            background: #f8fafc;
            color: #0f172a;
            font-weight: 600;
            text-align: left;
            padding: 0.75rem 0.85rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.8125rem;
        }
        .admin-table tbody td {
            color: #1e293b;
            padding: 0.75rem 0.85rem;
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
        /* Select2 Theme Customization */
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--single {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            min-height: 44px !important;
            display: flex !important;
            align-items: center !important;
            padding: 0.35rem 0.5rem !important;
            background-color: #f8fafc !important;
            transition: all 0.2s ease;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 0.875rem !important;
            line-height: 1.5rem !important;
            padding-left: 0.25rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 0.75rem !important;
        }
        .select2-dropdown {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.875rem !important;
            overflow: hidden !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            z-index: 9999 !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #10b981 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
        }
        .select2-search--dropdown {
            padding: 0.5rem !important;
        }
        .select2-search__field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding: 0.45rem 0.65rem !important;
            font-size: 0.875rem !important;
            outline: none !important;
        }
        .select2-search__field:focus {
            border-color: #10b981 !important;
        }
        .select2-results__option {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #10b981 !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 antialiased">
<div class="min-h-screen flex">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div id="mobileSidebarOverlay" class="fixed inset-0 bg-slate-900/40 z-30 hidden md:hidden"></div>

    <div id="mainShell" class="flex-1 flex flex-col transition-all duration-200 md:pl-72 min-w-0">
        <?php include __DIR__ . '/partials/header.php'; ?>
        <main class="flex-1 overflow-x-hidden bg-gray-50 p-3 md:p-6">
            <div class="max-w-full overflow-x-auto">
                <?php 
                $superPagePath = __DIR__ . '/pages/' . $currentPage . '.php';
                $adminPagePath = __DIR__ . '/../admin/pages/' . $currentPage . '.php';

                if (file_exists($superPagePath)) {
                    include $superPagePath;
                } elseif (file_exists($adminPagePath)) {
                    include $adminPagePath;
                } else {
                    echo "<p class='p-4 text-slate-500'>Sahifa topilmadi.</p>";
                }
                ?>
            </div>
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
