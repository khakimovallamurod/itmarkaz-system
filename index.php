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
    <title>IT-Markaz | Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <!-- External Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
    
    <style>
        :root {
            --primary: #059669;
            --primary-light: #10b981;
            --bg-light: #f1f5f9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            overflow: hidden;
            cursor: none;
        }

        /* Custom Cursor */
        #cursor-dot {
            width: 6px;
            height: 6px;
            background-color: var(--primary);
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
        }

        #cursor-outline {
            width: 38px;
            height: 38px;
            border: 2px solid rgba(5, 150, 105, 0.3);
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9998;
            transition: transform 0.1s ease-out;
        }

        /* Particles */
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        /* Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            transform-style: preserve-3d;
        }

        /* Logo Floating Animation */
        .logo-float {
            animation: float 6s ease-in-out infinite;
            filter: drop-shadow(0 15px 15px rgba(0,0,0,0.1));
            transform: translateZ(60px);
        }

        @keyframes float {
            0%, 100% { transform: translateZ(60px) translateY(0); }
            50% { transform: translateZ(60px) translateY(-15px); }
        }

        /* Input Styling */
        .input-field {
            background: rgba(255, 255, 255, 0.6);
            border: 1.5px solid #e2e8f0;
            transition: all 0.3s;
        }

        .input-field:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1);
        }

        .btn-premium {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            box-shadow: 0 10px 20px -5px rgba(5, 150, 105, 0.3);
            transform: translateZ(40px);
        }

        .btn-premium:hover {
            box-shadow: 0 15px 25px -5px rgba(5, 150, 105, 0.4);
            filter: brightness(1.05);
        }

        /* Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            animation: revealUp 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        @keyframes revealUp {
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            #cursor-dot, #cursor-outline { display: none; }
            body { cursor: auto; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative select-none">
    <div id="cursor-dot"></div>
    <div id="cursor-outline"></div>
    <div id="particles-js"></div>

    <main class="relative z-10 w-full max-w-[480px] p-6">
        <div class="glass-card rounded-[48px] p-12 reveal" data-tilt data-tilt-max="7" data-tilt-speed="800" data-tilt-perspective="1000">
            <!-- Unified Content in One Card -->
            <div class="flex flex-col items-center mb-10">
                <div class="logo-float mb-6">
                    <img src="assets/images/logo.png" alt="Logo" class="w-48 h-48 object-contain"
                         onerror="this.classList.add('hidden'); document.getElementById('fallback').classList.remove('hidden');">
                    <div id="fallback" class="hidden text-7xl font-black text-emerald-600 tracking-tighter">IT</div>
                </div>
                
                <h1 class="text-3xl font-black text-slate-800 tracking-tight" style="transform: translateZ(30px);">Xush Kelibsiz</h1>
                <p class="text-slate-500 font-medium mt-2" style="transform: translateZ(20px);">Tizimga kirish uchun ma'lumotlarni kiriting</p>
            </div>

            <form id="loginForm" class="space-y-6">
                <div class="space-y-1.5" style="transform: translateZ(25px);">
                    <label class="text-[11px] uppercase font-bold text-slate-400 ml-4 tracking-widest">Login</label>
                    <div class="relative group">
                        <i class="fa-solid fa-user absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-600 transition-colors"></i>
                        <input name="username" type="text" required
                               class="input-field w-full pl-12 pr-4 py-4 rounded-2xl outline-none text-slate-700 font-semibold text-base" 
                               placeholder="Admin login">
                    </div>
                </div>

                <div class="space-y-1.5" style="transform: translateZ(25px);">
                    <label class="text-[11px] uppercase font-bold text-slate-400 ml-4 tracking-widest">Parol</label>
                    <div class="relative group">
                        <i class="fa-solid fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-600 transition-colors"></i>
                        <input name="password" type="password" required
                               class="input-field w-full pl-12 pr-4 py-4 rounded-2xl outline-none text-slate-700 font-semibold text-base" 
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn-premium w-full py-4 rounded-2xl text-white font-bold text-lg flex items-center justify-center gap-3 transition-all active:scale-95 group">
                        <span>Tizimga kirish</span>
                        <i class="fa-solid fa-arrow-right-long text-sm group-hover:translate-x-1.5 transition-transform"></i>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Custom Cursor
        const dot = document.getElementById('cursor-dot');
        const outline = document.getElementById('cursor-outline');
        window.addEventListener('mousemove', (e) => {
            dot.style.left = `${e.clientX}px`;
            dot.style.top = `${e.clientY}px`;
            outline.animate({ left: `${e.clientX}px`, top: `${e.clientY}px` }, { duration: 400, fill: "forwards" });
        });

        // Particles
        particlesJS('particles-js', {
            "particles": {
                "number": { "value": 50, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#059669" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.1 },
                "size": { "value": 3 },
                "line_linked": { "enable": true, "distance": 150, "color": "#059669", "opacity": 0.05, "width": 1 },
                "move": { "enable": true, "speed": 0.8 }
            }
        });

        // Login Logic
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

            const fd = new FormData(form);
            try {
                const res = await fetch('api/auth.php?action=login', { method: 'POST', body: fd });
                const data = await res.json();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    icon: data.success ? 'success' : 'error',
                    title: data.message
                });
                if (data.success && data.data.redirect) {
                    setTimeout(() => window.location.href = data.data.redirect, 500);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        });
    </script>
</body>
</html>
