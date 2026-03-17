<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Iniciar Sesión</title>
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

        /* ── Fondo decorativo ── */
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

        /* ── Patrón de puntos ── */
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
            max-width: 440px;
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

        /* ── Marca ── */
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

        /* ── Tarjeta ── */
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

        /* ── Inputs ── */
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

        .mb-field {
            margin-bottom: 1.25rem;
        }

        /* ── Botón principal ── */
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

        /* ── Spinner dentro del botón ── */
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

        /* ── Alertas ── */
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

        /* ── Pie ── */
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
            color: var(--border);
            font-size: .8rem;
            color: var(--muted);
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

        <!-- Marca -->
        <div class="brand">
            <div class="brand-icon">🍳</div>
            <h1>SmartChef</h1>
            <p>Tu cocina, tus recetas</p>
        </div>

        <!-- Tarjeta de login -->
        <div class="card">
            <h2>Bienvenido de vuelta</h2>

            <!-- Alerta de error -->
            <div class="alert-box alert-error" id="alertError"></div>
            <!-- Alerta de éxito -->
            <div class="alert-box alert-success" id="alertSuccess"></div>

            <form id="loginForm" novalidate>

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
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required>
                </div>

                <button type="submit" class="btn-brand" id="btnLogin">
                    Iniciar sesión
                </button>

            </form>

            <div class="divider">o</div>

            <div class="card-foot">
                ¿No tienes cuenta? <a href="register.php">Regístrate gratis</a>
            </div>
        </div>

    </div>

    <script>
        const form = document.getElementById('loginForm');
        const btnLogin = document.getElementById('btnLogin');
        const alertError = document.getElementById('alertError');
        const alertOk = document.getElementById('alertSuccess');

        function showError(msg) {
            alertError.textContent = msg;
            alertError.style.display = 'block';
            alertOk.style.display = 'none';
        }

        function showSuccess(msg) {
            alertOk.textContent = msg;
            alertOk.style.display = 'block';
            alertError.style.display = 'none';
        }

        function setLoading(loading) {
            btnLogin.disabled = loading;
            btnLogin.innerHTML = loading ?
                '<span class="spinner"></span>Entrando...' :
                'Iniciar sesión';
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            alertError.style.display = 'none';
            alertOk.style.display = 'none';

            const correo = document.getElementById('correo').value.trim();
            const password = document.getElementById('password').value.trim();

            // Validación en cliente
            if (!correo || !password) {
                showError('Por favor completa todos los campos.');
                return;
            }

            setLoading(true);

            try {
                const formData = new FormData();
                formData.append('correo', correo);
                formData.append('password', password);

                const res = await fetch('../api/auth/login.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (res.ok) {
                    showSuccess(`¡Bienvenido, ${data.nombre}! Redirigiendo...`);
                    setTimeout(() => window.location.href = 'dashboard.php', 1200);
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