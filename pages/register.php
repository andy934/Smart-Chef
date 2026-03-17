<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Crear Cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #E85D2F;
            --brand-dark: #C44A1F;
            --cream: #FDF8F3;
            --ink: #1A1208;
            --muted: #8C7B6B;
            --border: #EAE0D5;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--cream);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 10% 20%, rgba(232, 93, 47, .08) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 90% 80%, rgba(232, 93, 47, .06) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(26, 18, 8, .06) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        .card-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            animation: fadeUp .5s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-icon {
            width: 52px;
            height: 52px;
            background: var(--brand);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .75rem;
            font-size: 1.6rem;
        }

        .brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--ink);
            line-height: 1;
        }

        .brand p {
            font-size: .85rem;
            color: var(--muted);
            margin-top: .3rem;
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 4px 40px rgba(26, 18, 8, .07);
        }

        .card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--ink);
            margin-bottom: 1.75rem;
        }

        .form-label {
            font-size: .8rem;
            font-weight: 500;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: .4rem;
        }

        .form-control {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: .75rem 1rem;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem;
            color: var(--ink);
            background: var(--cream);
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(232, 93, 47, .12);
            background: #fff;
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            font-size: .8rem;
            color: #dc3545;
            margin-top: .3rem;
        }

        .mb-field {
            margin-bottom: 1.25rem;
        }

        /* ── Indicador de fortaleza de contraseña ── */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: var(--border);
            margin-top: .5rem;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 2px;
            width: 0%;
            transition: width .3s, background .3s;
        }

        .strength-label {
            font-size: .75rem;
            color: var(--muted);
            margin-top: .25rem;
        }

        .btn-brand {
            width: 100%;
            padding: .85rem;
            background: var(--brand);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background .2s, transform .1s;
            margin-top: .5rem;
        }

        .btn-brand:hover {
            background: var(--brand-dark);
        }

        .btn-brand:active {
            transform: scale(.98);
        }

        .btn-brand:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            vertical-align: middle;
            margin-right: .4rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .alert-box {
            border-radius: 10px;
            padding: .85rem 1rem;
            font-size: .9rem;
            margin-bottom: 1.25rem;
            display: none;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-error ul {
            margin: .4rem 0 0 1rem;
        }

        .card-foot {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .88rem;
            color: var(--muted);
        }

        .card-foot a {
            color: var(--brand);
            text-decoration: none;
            font-weight: 500;
        }

        .card-foot a:hover {
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: var(--muted);
            font-size: .8rem;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
    </style>
</head>

<body>

    <div class="card-wrap">

        <div class="brand">
            <div class="brand-icon">🍳</div>
            <h1>SmartChef</h1>
            <p>Crea tu cuenta y empieza a compartir recetas</p>
        </div>

        <div class="card">
            <h2>Crear cuenta</h2>

            <div class="alert-box alert-error" id="alertError"></div>
            <div class="alert-box alert-success" id="alertSuccess"></div>

            <form id="registerForm" novalidate>

                <div class="mb-field">
                    <label class="form-label" for="nombre">Nombre completo</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        class="form-control"
                        placeholder="Tu nombre"
                        autocomplete="name"
                        required>
                </div>

                <div class="mb-field">
                    <label class="form-label" for="correo">Correo electrónico</label>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        class="form-control"
                        placeholder="tu@correo.com"
                        autocomplete="email"
                        required>
                </div>

                <div class="mb-field">
                    <label class="form-label" for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Mínimo 6 caracteres"
                        autocomplete="new-password"
                        required>
                    <!-- Indicador de fortaleza -->
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-label" id="strengthLabel"></div>
                </div>

                <div class="mb-field">
                    <label class="form-label" for="password_confirm">Confirmar contraseña</label>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        class="form-control"
                        placeholder="Repite tu contraseña"
                        autocomplete="new-password"
                        required>
                </div>

                <button type="submit" class="btn-brand" id="btnRegister">
                    Crear cuenta
                </button>

            </form>

            <div class="divider">o</div>

            <div class="card-foot">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
            </div>
        </div>

    </div>

    <script>
        const form = document.getElementById('registerForm');
        const btnReg = document.getElementById('btnRegister');
        const alertError = document.getElementById('alertError');
        const alertOk = document.getElementById('alertSuccess');
        const pwdInput = document.getElementById('password');
        const fill = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');

        // ── Indicador de fortaleza de contraseña ──────────────────────
        pwdInput.addEventListener('input', () => {
            const v = pwdInput.value;
            let score = 0;
            if (v.length >= 6) score++;
            if (v.length >= 10) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;

            const levels = [{
                    pct: '0%',
                    color: '',
                    text: ''
                },
                {
                    pct: '25%',
                    color: '#ef4444',
                    text: 'Muy débil'
                },
                {
                    pct: '50%',
                    color: '#f97316',
                    text: 'Débil'
                },
                {
                    pct: '75%',
                    color: '#eab308',
                    text: 'Aceptable'
                },
                {
                    pct: '90%',
                    color: '#22c55e',
                    text: 'Fuerte'
                },
                {
                    pct: '100%',
                    color: '#16a34a',
                    text: 'Muy fuerte'
                },
            ];

            const lvl = levels[score] || levels[0];
            fill.style.width = lvl.pct;
            fill.style.background = lvl.color;
            label.textContent = lvl.text;
            label.style.color = lvl.color || 'var(--muted)';
        });

        // ── Helpers ───────────────────────────────────────────────────
        function showError(errores) {
            if (typeof errores === 'string') {
                alertError.innerHTML = errores;
            } else {
                const items = errores.map(e => `<li>${e}</li>`).join('');
                alertError.innerHTML = `<ul>${items}</ul>`;
            }
            alertError.style.display = 'block';
            alertOk.style.display = 'none';
        }

        function showSuccess(msg) {
            alertOk.textContent = msg;
            alertOk.style.display = 'block';
            alertError.style.display = 'none';
        }

        function setLoading(loading) {
            btnReg.disabled = loading;
            btnReg.innerHTML = loading ?
                '<span class="spinner"></span>Creando cuenta...' :
                'Crear cuenta';
        }

        // ── Submit ────────────────────────────────────────────────────
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            alertError.style.display = 'none';
            alertOk.style.display = 'none';

            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const password = document.getElementById('password').value;
            const password_confirm = document.getElementById('password_confirm').value;

            // Validación en cliente
            const errores = [];
            if (!nombre) errores.push('El nombre es obligatorio.');
            if (!correo) errores.push('El correo es obligatorio.');
            if (!password) errores.push('La contraseña es obligatoria.');
            else if (password.length < 6) errores.push('La contraseña debe tener al menos 6 caracteres.');
            if (password !== password_confirm) errores.push('Las contraseñas no coinciden.');

            if (errores.length) {
                showError(errores);
                return;
            }

            setLoading(true);

            try {
                const formData = new FormData();
                formData.append('nombre', nombre);
                formData.append('correo', correo);
                formData.append('password', password);
                formData.append('password_confirm', password_confirm);

                const res = await fetch('../api/auth/register.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (res.ok) {
                    showSuccess('¡Cuenta creada! Redirigiendo al login...');
                    form.reset();
                    fill.style.width = '0%';
                    label.textContent = '';
                    setTimeout(() => window.location.href = 'login.php', 1500);
                } else if (data.errores) {
                    showError(data.errores);
                } else {
                    showError(data.error || 'Ocurrió un error. Intenta de nuevo.');
                }
            } catch (err) {
                showError('No se pudo conectar con el servidor.');
            } finally {
                setLoading(false);
            }
        });
    </script>

</body>

</html>