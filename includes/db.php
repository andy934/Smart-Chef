<?php
// ============================================================
//  SmartChef — Conexión a la base de datos
//  Archivo: includes/db.php
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'smartchef');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lanza excepciones en errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Resultados como arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En producción nunca mostrar el error real al usuario
    http_response_code(500);
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}
