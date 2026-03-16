<?php
// ============================================================
//  SmartChef — API: Inicio de sesión
//  Archivo: api/auth/login.php
//  Método:  POST
//  Body:    correo, password
// ============================================================

require_once '../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido.']));
}

$correo   = trim($_POST['correo']   ?? '');
$password = trim($_POST['password'] ?? '');

// ── Validaciones básicas ──────────────────────────────────────
if (empty($correo) || empty($password)) {
    http_response_code(422);
    die(json_encode(['error' => 'Correo y contraseña son obligatorios.']));
}

// ── Buscar usuario por correo ─────────────────────────────────
$stmt = $pdo->prepare('SELECT id, nombre, password_hash FROM usuarios WHERE correo = ?');
$stmt->execute([$correo]);
$usuario = $stmt->fetch();

// Verificar credenciales
// Nota: el mensaje de error es genérico a propósito (seguridad)
// No se indica si falló el correo o la contraseña
if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Credenciales incorrectas.']));
}

// ── Iniciar sesión ────────────────────────────────────────────
session_start();
session_regenerate_id(true);   // Previene session fixation attacks

$_SESSION['usuario_id']     = $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];

echo json_encode([
    'mensaje' => 'Sesión iniciada.',
    'nombre'  => $usuario['nombre']
]);
