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
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    
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
                <div class="logo-3d-wrapper relative w-full aspect-square max-w-[300px] mb-2 overflow-visible flex items-center justify-center" 
                     style="transform: translateZ(80px); transition: all 0.5s ease-out;">
                    <div id="logo-3d-container" class="w-full h-full opacity-0 animate-[fadeIn_1.5s_ease-out_forwards]" 
                         style="min-height: 200px; filter: drop-shadow(0 20px 30px rgba(16, 185, 129, 0.15));"></div>
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

        // 3D Logo Animation
        (() => {
            const container = document.getElementById('logo-3d-container');
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 1000);
            const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            
            // Accurate sizing
            const updateSize = () => {
                const w = container.offsetWidth || 300;
                const h = container.offsetHeight || 300;
                renderer.setSize(w, h);
                camera.aspect = w / h;
                camera.updateProjectionMatrix();
            };

            renderer.setPixelRatio(window.devicePixelRatio);
            container.appendChild(renderer.domElement);
            updateSize();

            camera.position.z = 12;

            // Lighting
            scene.add(new THREE.AmbientLight(0xffffff, 0.8));
            const p1 = new THREE.PointLight(0x10b981, 1);
            p1.position.set(4, 4, 4);
            scene.add(p1);
            const p2 = new THREE.PointLight(0x224499, 1);
            p2.position.set(-4, -4, 4);
            scene.add(p2);

            // Materials
            const greenMat = new THREE.MeshStandardMaterial({ 
                color: 0x56c29a, 
                metalness: 0.2, 
                roughness: 0.3,
                emissive: 0x56c29a,
                emissiveIntensity: 0.1
            });
            const blueMat = new THREE.MeshStandardMaterial({ 
                color: 0x224499, 
                metalness: 0.5, 
                roughness: 0.2
            });

            const logoGroup = new THREE.Group();
            scene.add(logoGroup);

            // 1. Green Sphere (Head)
            const head = new THREE.Mesh(new THREE.SphereGeometry(0.55, 32, 32), greenMat);
            head.position.y = 1.8;
            logoGroup.add(head);

            // 2. Green Wing (Left)
            const wingShape = new THREE.Shape();
            wingShape.moveTo(0, 0);
            wingShape.lineTo(-1.0, 0);
            wingShape.quadraticCurveTo(-1.0, -0.6, 0, -0.6);
            const wing = new THREE.Mesh(
                new THREE.ExtrudeGeometry(wingShape, { depth: 0.15, bevelEnabled: true, bevelSize: 0.02 }),
                greenMat
            );
            wing.position.set(-0.48, 0.55, 0.05); // Precisely at the boundary, slightly forward to avoid merging
            logoGroup.add(wing);

        // 3. Blue stylized Body (Thinner and smaller)
            const bodyShape = new THREE.Shape();
            bodyShape.moveTo(-0.25, -1.3);
            bodyShape.lineTo(-0.25, 0.5);
            bodyShape.quadraticCurveTo(-0.25, 0.65, -0.1, 0.65);
            bodyShape.lineTo(0.3, 0.65);
            bodyShape.quadraticCurveTo(1.1, 0.65, 1.1, -0.1);
            bodyShape.lineTo(1.1, -0.3);
            bodyShape.lineTo(0.25, -0.3);
            bodyShape.lineTo(0.25, -0.05);
            bodyShape.quadraticCurveTo(0.25, 0.15, 0.1, 0.15);
            bodyShape.lineTo(0.1, -1.3);
            bodyShape.quadraticCurveTo(0.1, -1.8, 1.0, -1.8);
            bodyShape.lineTo(1.0, -1.95);
            bodyShape.quadraticCurveTo(-0.25, -1.95, -0.25, -1.3);

            const body = new THREE.Mesh(
                new THREE.ExtrudeGeometry(bodyShape, { depth: 0.15, bevelEnabled: true, bevelSize: 0.03 }),
                blueMat
            );
            body.position.set(-0.15, -0.1, 0);
            logoGroup.add(body);

        // 4. Green "IT" text (Even smaller and better positioned)
            const itGroup = new THREE.Group();
            const iStem = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.45, 0.08), greenMat);
            iStem.position.x = 0.45;
            itGroup.add(iStem);

            const tTop = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.08, 0.08), greenMat);
            tTop.position.set(0.85, 0.18, 0);
            itGroup.add(tTop);
            
            const tStem = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.38, 0.08), greenMat);
            tStem.position.set(0.85, -0.05, 0);
            itGroup.add(tStem);
            
            itGroup.position.set(0.12, -0.75, 0);
            logoGroup.add(itGroup);

            // Entry Animation State
            head.position.y = 8;
            wing.position.x = -8;
            body.position.x = 8;
            body.rotation.y = Math.PI;
            itGroup.position.x = 8;
            itGroup.visible = false;

            let start = null;
            const duration = 2500;

            function animate(now) {
                if (!start) start = now;
                const elapsed = now - start;
                const p = Math.min(elapsed / duration, 1);
                const ease = 1 - Math.pow(1 - p, 4);

                head.position.y = 8 - (8 - 1.8) * ease;
                wing.position.x = -8 + (8 - (-0.42)) * ease;
                body.position.x = 8 - (8 - (-0.15)) * ease;
                body.rotation.y = Math.PI * (1 - ease);
                
                if (p > 0.5) {
                    itGroup.visible = true;
                    itGroup.position.x = 8 - (8 - 0.15) * ((p - 0.5) / 0.5);
                }

                if (p === 1) {
                    logoGroup.position.y = Math.sin(now * 0.002) * 0.1;
                    logoGroup.rotation.y = Math.sin(now * 0.001) * 0.1;
                    logoGroup.rotation.x = Math.cos(now * 0.001) * 0.05;
                }

                renderer.render(scene, camera);
                requestAnimationFrame(animate);
            }
            requestAnimationFrame(animate);

            window.addEventListener('resize', updateSize);
        })();

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
