<?php
// ============================================================
//  SmartChef — API: Cierre de sesión
//  Archivo: api/auth/logout.php
//  Método:  GET (basta con visitar la URL)
// ============================================================

session_start();
session_unset();
session_destroy();

// Redirigir al login después de cerrar sesión
header('Location: ../../pages/login.php');
exit;
