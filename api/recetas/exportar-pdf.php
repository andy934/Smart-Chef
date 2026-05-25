<?php
// ============================================================
//  SmartChef — API: Exportar receta a PDF con FPDF
//  Archivo: api/recetas/exportar-pdf.php
//  Método:  GET
//  Params:  id (receta_id)
// ============================================================

require_once '../../includes/db.php';
require_once '../../includes/fpdf.php';

// ✅ Buffer para evitar que warnings arruinen el PDF
ob_start();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(422);
    die('Receta no válida.');
}

// Obtener receta
$stmt = $pdo->prepare(
    'SELECT r.*, u.nombre AS autor
     FROM recetas r JOIN usuarios u ON r.usuario_id = u.id
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

// ── Helpers ───────────────────────────────────────────────────
// FPDF solo soporta Latin1 — convertir UTF-8 ignorando caracteres no convertibles
function utf8($str)
{
    // Primero limpiar emojis y caracteres no Latin1
    $str = preg_replace('/[^\x00-\x7F\xA0-\xFF]/u', '', $str);
    $result = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $str);
    return $result !== false ? $result : $str;
}

// ── Clase PDF personalizada ───────────────────────────────────
class RecetaPDF extends FPDF
{
    public $tituloReceta = '';

    function Header()
    {
        // Franja naranja de marca
        $this->SetFillColor(232, 93, 47);
        $this->Rect(0, 0, 210, 12, 'F');
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(255, 255, 255);
        $this->SetY(3);
        $this->Cell(0, 6, utf8('🍳 SmartChef'), 0, 0, 'C');
        $this->Ln(14);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(140, 123, 107);
        $this->Cell(0, 10, utf8('Generado por SmartChef  ·  Página ') . $this->PageNo(), 0, 0, 'C');
    }

    function SectionTitle($texto)
    {
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(232, 93, 47);
        $this->SetFillColor(253, 248, 243);
        $this->Cell(0, 8, utf8($texto), 0, 1, 'L', true);
        $this->SetDrawColor(234, 224, 213);
        $this->Line($this->GetX(), $this->GetY(), $this->GetX() + 175, $this->GetY());
        $this->Ln(3);
    }
}

// ── Generar PDF ───────────────────────────────────────────────
$pdf = new RecetaPDF('P', 'mm', 'A4');
$pdf->SetAuthor(utf8($receta['autor']));
$pdf->SetTitle(utf8($receta['titulo']));
$pdf->SetCreator('SmartChef');
$pdf->AddPage();
$pdf->SetMargins(18, 18, 18);
$pdf->SetAutoPageBreak(true, 18);

// ── Título de la receta ───────────────────────────────────────
$pdf->SetFont('Times', 'B', 22);
$pdf->SetTextColor(26, 18, 8);
$pdf->MultiCell(0, 10, utf8($receta['titulo']), 0, 'L');
$pdf->Ln(2);

// ── Meta (autor, tiempo, fecha) ───────────────────────────────
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(140, 123, 107);
$fecha = date('d/m/Y', strtotime($receta['created_at']));
$pdf->Cell(0, 6, utf8("Autor: {$receta['autor']}   ·   Tiempo: {$receta['tiempo_min']} min   ·   {$fecha}"), 0, 1);
$pdf->Ln(2);

// ── Etiquetas ─────────────────────────────────────────────────
if (!empty($etiquetas)) {
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(232, 93, 47);
    $pdf->SetFillColor(255, 247, 237);
    $pdf->SetDrawColor(253, 213, 196);
    foreach ($etiquetas as $etq) {
        $w = $pdf->GetStringWidth(utf8($etq)) + 6;
        $pdf->Cell($w, 6, utf8($etq), 1, 0, 'C', true);
        $pdf->Cell(2, 6, '', 0); // espacio entre tags
    }
    $pdf->Ln(10);
} else {
    $pdf->Ln(4);
}

// ── Ingredientes ──────────────────────────────────────────────
$pdf->SectionTitle('Ingredientes');
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(26, 18, 8);

foreach ($ingredientes as $i => $ing) {
    // Fondo alternado
    if ($i % 2 === 0) {
        $pdf->SetFillColor(253, 248, 243);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    $nombre   = utf8($ing['nombre']);
    $cantidad = utf8($ing['cantidad'] ?? '');

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(26, 18, 8);
    $pdf->Cell(120, 7, $nombre, 0, 0, 'L', true);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(232, 93, 47);
    $pdf->Cell(55, 7, $cantidad, 0, 1, 'R', true);
}
$pdf->Ln(5);

// ── Pasos de preparación ──────────────────────────────────────
$pdf->SectionTitle('Preparación');
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(26, 18, 8);
// El segundo parámetro de MultiCell es el alto de línea (6mm ≈ interlineado cómodo)
$pdf->MultiCell(0, 7, utf8($receta['pasos']), 0, 'L');

// ── Descargar PDF ─────────────────────────────────────────────
$nombreArchivo = 'receta-' . $id . '-' . date('Ymd') . '.pdf';
ob_end_clean(); // ✅ Limpiar cualquier output previo antes de enviar el PDF
$pdf->Output('D', $nombreArchivo);
