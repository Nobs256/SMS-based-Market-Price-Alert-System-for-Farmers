<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\AuthService;
use App\LogService;

session_start();

// --- Authentication Check ---
$authService = new AuthService();
if (!$authService->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$logService = new LogService();
$logs = $logService->getAllLogs();

// Create PDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 15, 'SMS System - Broadcast History Logs', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'R');
$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(45, 10, 'Date & Time', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(120, 10, 'Details', 1, 1, 'C', true);

// Table Body
$pdf->SetFont('Arial', '', 9);
foreach ($logs as $log) {
    // Check for page break if row exceeds page length
    if ($pdf->GetY() > 270) {
        $pdf->AddPage();
        $pdf->Cell(45, 10, 'Date & Time', 1, 0, 'C', true);
        $pdf->Cell(25, 10, 'Status', 1, 0, 'C', true);
        $pdf->Cell(120, 10, 'Details', 1, 1, 'C', true);
    }

    $currentX = $pdf->GetX();
    $currentY = $pdf->GetY();

    // Move to the position of the third column (45 + 25 = 70)
    $pdf->SetX($currentX + 70);
    // Use MultiCell for the message to allow wrapping to the next line
    $pdf->MultiCell(120, 8, $log['message'], 1);
    $nextY = $pdf->GetY();
    $rowHeight = $nextY - $currentY;

    // Go back and print the first two cells with the synchronized row height
    $pdf->SetXY($currentX, $currentY);
    $pdf->Cell(45, $rowHeight, $log['created_at'], 1);
    $pdf->Cell(25, $rowHeight, ucfirst($log['status']), 1, 0, 'C');
    $pdf->SetY($nextY); // Move cursor to the start of the next row
}

$pdf->Output('D', 'System_Broadcast_Logs_' . date('Y-m-d') . '.pdf');