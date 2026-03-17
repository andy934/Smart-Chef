<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/auth.css" rel="stylesheet">
</head>

<body>

    <div class="card-wrap">

        <div class="brand">
            <div class="brand-icon">🍳</div>
            <h1>SmartChef</h1>
            <p>Tu cocina, tus recetas</p>
        </div>

        <div class="card">
            <h2>Bienvenido de vuelta</h2>

            <div class="alert-box alert-error" id="alertError"></div>
            <div class="alert-box alert-success" id="alertSuccess"></div>

            <form id="loginForm" novalidate>

                <div class="mb-field">
                    <label class="form-label" for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-control"
                        placeholder="tu@correo.com" autocomplete="email" required>
                </div>

                <div class="mb-field">
                    <label class="form-label" for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="••••••••" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn-brand btn-brand-block" id="btnLogin">
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