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
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
            cursor: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
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

        /* 3-Part Logo WOW Animation */
        .logo-container {
            position: relative;
            width: 160px;
            height: 160px;
            margin: 0 auto;
            filter: drop-shadow(0 25px 35px rgba(5, 150, 105, 0.3));
            transform-style: preserve-3d;
            animation: floatLogo 5s ease-in-out infinite 2.5s;
        }

        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); filter: drop-shadow(0 35px 45px rgba(5, 150, 105, 0.4)); }
        }

        .logo-container::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('assets/images/logo.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            animation: fadeInSolid 0.8s ease-in-out 2.2s forwards;
            z-index: 10;
        }

        @keyframes fadeInSolid {
            to { opacity: 1; }
        }

        .logo-part {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('assets/images/logo.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            /* Using a beautiful spring/bounce cubic-bezier */
            animation: assembleLogo 2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            z-index: 5;
        }

        .logo-part-1 {
            clip-path: polygon(0 0, 100% 0, 55% 45%, 0 100%);
            /* Comes from top-left, spins a full circle, starts tiny */
            transform: translate(-200px, -250px) rotate(-360deg) scale(0.1);
            animation-delay: 0.1s;
        }

        .logo-part-2 {
            clip-path: polygon(100% 0, 100% 100%, 55% 45%);
            /* Comes from right, spins a full circle */
            transform: translate(250px, -50px) rotate(360deg) scale(0.1);
            animation-delay: 0.3s;
        }

        .logo-part-3 {
            clip-path: polygon(0 100%, 55% 45%, 100% 100%);
            /* Comes from bottom-left, spins half circle */
            transform: translate(-100px, 250px) rotate(180deg) scale(0.1);
            animation-delay: 0.5s;
        }

        @keyframes assembleLogo {
            0% {
                opacity: 0;
                filter: blur(15px);
            }
            40% {
                opacity: 1;
                filter: blur(5px);
            }
            100% {
                opacity: 1;
                transform: translate(0, 0) rotate(0deg) scale(1);
                filter: blur(0px);
            }
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

    <main class="relative z-10 w-full max-w-[520px] p-4 sm:p-6 flex items-center justify-center">
        <div class="glass-card w-full rounded-[48px] p-8 sm:p-12 reveal shadow-2xl transition-all duration-500 hover:shadow-emerald-500/10" 
             data-tilt data-tilt-max="5" data-tilt-speed="400" data-tilt-perspective="1200">
            
            <!-- Logo Section -->
            <div class="flex flex-col items-center justify-center text-center">
                <div class="logo-3d-wrapper relative w-full aspect-square max-w-[200px] mb-2 overflow-visible flex items-center justify-center" 
                     style="transform: translateZ(80px); transition: all 0.5s ease-out;">
                    <div class="logo-container">
                        <div class="logo-part logo-part-1"></div>
                        <div class="logo-part logo-part-2"></div>
                        <div class="logo-part logo-part-3"></div>
                    </div>
                </div>
                
                <div class="mt-4 mb-8" style="transform: translateZ(40px);">
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-800 tracking-tight mb-2 leading-none">Xush Kelibsiz</h1>
                    <p class="text-slate-500 font-medium text-sm sm:text-base opacity-80">Tizimga kirish uchun ma'lumotlarni kiriting</p>
                </div>
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
