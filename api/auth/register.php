<?php
// ============================================================
//  SmartChef — API: Registro de usuario
//  Archivo: api/auth/register.php
//  Método:  POST
//  Body:    nombre, correo, password, password_confirm
// ============================================================

require_once '../../includes/db.php';

header('Content-Type: application/json');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido.']));
}

// Leer y limpiar datos del formulario
$nombre           = trim($_POST['nombre']           ?? '');
$correo           = trim($_POST['correo']           ?? '');
$password         = trim($_POST['password']         ?? '');
$password_confirm = trim($_POST['password_confirm'] ?? '');

// ── Validaciones ─────────────────────────────────────────────
$errores = [];

if (empty($nombre)) {
    $errores[] = 'El nombre es obligatorio.';
}

if (empty($correo)) {
    $errores[] = 'El correo es obligatorio.';
} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo no tiene un formato válido.';
}

if (empty($password)) {
    $errores[] = 'La contraseña es obligatoria.';
} elseif (strlen($password) < 6) {
    $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
}

if ($password !== $password_confirm) {
    $errores[] = 'Las contraseñas no coinciden.';
}

if (!empty($errores)) {
    http_response_code(422);
    die(json_encode(['errores' => $errores]));
}

// ── Verificar que el correo no esté registrado ────────────────
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE correo = ?');
$stmt->execute([$correo]);

if ($stmt->fetch()) {
    http_response_code(409);
    die(json_encode(['error' => 'Este correo ya está registrado.']));
}

// ── Crear el usuario ──────────────────────────────────────────
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO usuarios (nombre, correo, password_hash) VALUES (?, ?, ?)');
$stmt->execute([$nombre, $correo, $hash]);

http_response_code(201);
echo json_encode(['mensaje' => 'Cuenta creada exitosamente.']);
