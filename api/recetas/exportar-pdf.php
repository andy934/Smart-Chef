<?php
// ============================================================
//  SmartChef — API: Exportar receta a PDF
//  Archivo: api/recetas/exportar-pdf.php
//  Método:  GET
//  Params:  id (receta_id)
// ============================================================

require_once '../../includes/db.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(422);
    die('Receta no válida.');
}

// Obtener receta
$stmt = $pdo->prepare(
    'SELECT r.*, u.nombre AS autor
     FROM recetas r
     JOIN usuarios u ON r.usuario_id = u.id
     WHERE r.id = ?'
);
$stmt->execute([$id]);
$receta = $stmt->fetch();

if (!$receta) {
    http_response_code(404);
    die('Receta no encontrada.');
}

// Obtener ingredientes
$stmtIng = $pdo->prepare('SELECT nombre, cantidad FROM ingredientes WHERE receta_id = ? ORDER BY id');
$stmtIng->execute([$id]);
$ingredientes = $stmtIng->fetchAll();

// Obtener etiquetas
$stmtEt = $pdo->prepare(
    'SELECT e.nombre FROM etiquetas e
     JOIN receta_etiquetas re ON re.etiqueta_id = e.id
     WHERE re.receta_id = ?'
);
$stmtEt->execute([$id]);
$etiquetas = array_column($stmtEt->fetchAll(), 'nombre');

// ── Generar HTML del PDF ──────────────────────────────────────
$titulo    = htmlspecialchars($receta['titulo']);
$autor     = htmlspecialchars($receta['autor']);
$tiempo    = $receta['tiempo_min'];
$pasos     = nl2br(htmlspecialchars($receta['pasos']));
$fecha     = date('d/m/Y', strtotime($receta['created_at']));
$etiqHtml  = !empty($etiquetas)
    ? implode('', array_map(fn($e) => "<span class='tag'>" . htmlspecialchars($e) . "</span>", $etiquetas))
    : '';

$ingHtml = '';
foreach ($ingredientes as $ing) {
    $nombre   = htmlspecialchars($ing['nombre']);
    $cantidad = htmlspecialchars($ing['cantidad'] ?? '');
    $ingHtml .= "<li><span class='ing-nombre'>$nombre</span>" . ($cantidad ? "<span class='ing-cantidad'>$cantidad</span>" : "") . "</li>";
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{$titulo} — SmartChef</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Georgia, serif; color: #1A1208; background: #fff; padding: 2.5rem; max-width: 720px; margin: 0 auto; }
  .header { border-bottom: 3px solid #E85D2F; padding-bottom: 1rem; margin-bottom: 1.5rem; }
  .brand  { font-size: .85rem; color: #E85D2F; font-weight: bold; letter-spacing: .1em; text-transform: uppercase; margin-bottom: .5rem; }
  h1      { font-size: 2rem; color: #1A1208; line-height: 1.2; margin-bottom: .5rem; }
  .meta   { font-size: .85rem; color: #8C7B6B; display: flex; gap: 1.5rem; flex-wrap: wrap; margin-top: .5rem; }
  .tags   { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: .75rem; }
  .tag    { background: #FFF3EE; border: 1px solid #fdd5c4; color: #E85D2F; border-radius: 20px; padding: .15rem .6rem; font-size: .75rem; font-family: Arial, sans-serif; }
  section { margin-bottom: 1.75rem; }
  h2      { font-size: 1.1rem; color: #E85D2F; border-bottom: 1px solid #EAE0D5; padding-bottom: .3rem; margin-bottom: .75rem; }
  ul.ing  { list-style: none; display: flex; flex-direction: column; gap: .35rem; }
  ul.ing li { display: flex; justify-content: space-between; padding: .45rem .75rem; background: #FDF8F3; border-radius: 6px; font-size: .9rem; }
  .ing-cantidad { color: #E85D2F; font-weight: bold; }
  .pasos-text { font-size: .95rem; line-height: 1.9; white-space: pre-line; color: #1A1208; }
  .footer { margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #EAE0D5; font-size: .78rem; color: #8C7B6B; text-align: center; }
  @media print {
    body { padding: 1rem; }
    .no-print { display: none; }
  }
</style>
</head>
<body>

<div class="no-print" style="margin-bottom:1.5rem;">
  <button onclick="window.print()" style="background:#E85D2F;color:#fff;border:none;border-radius:8px;padding:.6rem 1.5rem;font-size:.9rem;cursor:pointer;">
    🖨️ Imprimir / Guardar como PDF
  </button>
</div>

<div class="header">
  <div class="brand">🍳 SmartChef</div>
  <h1>{$titulo}</h1>
  <div class="meta">
    <span>👤 {$autor}</span>
    <span>⏱ {$tiempo} minutos</span>
    <span>📅 {$fecha}</span>
  </div>
  <div class="tags">{$etiqHtml}</div>
</div>

<section>
  <h2>Ingredientes</h2>
  <ul class="ing">{$ingHtml}</ul>
</section>

<section>
  <h2>Preparación</h2>
  <p class="pasos-text">{$pasos}</p>
</section>

<div class="footer">
  Generado por SmartChef · smartchef.local · {$fecha}
</div>

</body>
</html>
HTML;

// Devolver como página HTML imprimible (el navegador maneja el PDF)
header('Content-Type: text/html; charset=utf-8');
echo $html;
