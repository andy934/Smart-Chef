<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Crear Cuenta</title>
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
            <p>Crea tu cuenta y empieza a compartir recetas</p>
        </div>

        <div class="card">
            <h2>Crear cuenta</h2>

            <div class="alert-box alert-error" id="alertError"></div>
            <div class="alert-box alert-success" id="alertSuccess"></div>

            <form id="registerForm" novalidate>

                <div class="mb-field">
                    <label class="form-label" for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control"
                        placeholder="Tu nombre" autocomplete="name" required>
                </div>

                <div class="mb-field">
                    <label class="form-label" for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-control"
                        placeholder="tu@correo.com" autocomplete="email" required>
                </div>

                <div class="mb-field">
                    <label class="form-label" for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Mínimo 6 caracteres" autocomplete="new-password" required>
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-label" id="strengthLabel"></div>
                </div>

                <div class="mb-field">
                    <label class="form-label" for="password_confirm">Confirmar contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                        placeholder="Repite tu contraseña" autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn-brand btn-brand-block" id="btnRegister">
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

        // Indicador de fortaleza de contraseña
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

        function showError(errores) {
            if (typeof errores === 'string') {
                alertError.innerHTML = errores;
            } else {
                alertError.innerHTML = `<ul>${errores.map(e => `<li>${e}</li>`).join('')}</ul>`;
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

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            alertError.style.display = 'none';
            alertOk.style.display = 'none';

            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const password = document.getElementById('password').value;
            const password_confirm = document.getElementById('password_confirm').value;

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