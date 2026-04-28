<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/index.php');
    exit;
}
?>
<!doctype html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT-Markaz Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
<div class="w-full max-w-md min-h-[560px] bg-white rounded-2xl shadow-lg p-6 flex flex-col justify-center">
    <div class="mb-5 flex flex-col items-center">
        <img
            src="assets/images/logo.png"
            alt="IT-Markaz logotipi"
            class="h-36 w-36 object-contain rounded-xl "
            onerror="this.classList.add('hidden'); document.getElementById('logoFallback').classList.remove('hidden');"
        >
        <div id="logoFallback" class="hidden h-24 w-24 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-2xl items-center justify-center flex">IT</div>
    </div>
    <h1 class="text-2xl font-bold text-emerald-900 text-center">IT-Markaz Admin Login</h1>
    <p class="text-sm text-slate-500 mt-1 text-center">Login va parol bilan tizimga kiring</p>

    <form id="loginForm" class="mt-8 space-y-4">
        <input name="username" placeholder="Login" class="w-full border rounded-lg p-3" required>
        <input type="password" name="password" placeholder="Parol" class="w-full border rounded-lg p-3" required>
        <button class="w-full bg-emerald-800 text-white p-3 rounded-lg hover:bg-emerald-700">Kirish</button>
    </form>
</div>

<script>
const form = document.getElementById('loginForm');
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const res = await fetch('api/auth.php?action=login', { method: 'POST', body: fd });
    const data = await res.json();

    Swal.fire({
        toast: true,
        position: 'top-end',
        timer: 1800,
        showConfirmButton: false,
        icon: data.success ? 'success' : 'error',
        title: data.message
    });

    if (data.success && data.data.redirect) {
        setTimeout(() => window.location.href = data.data.redirect, 700);
    }
});
</script>
</body>
</html>
