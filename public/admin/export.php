<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\AuthService;
use App\FarmerService;
use FPDF;

session_start();

// --- Authentication Check ---
$authService = new AuthService();
if (!$authService->isLoggedIn()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied.');
}

$format = $_GET['format'] ?? 'csv';
$farmerService = new FarmerService();
$farmers = $farmerService->getAllFarmers();

switch ($format) {
    case 'csv':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="farmers-export-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Add header row
        fputcsv($output, ['ID', 'Name', 'Phone Number', 'Language']);

        // Add data rows
        foreach ($farmers as $farmer) {
            fputcsv($output, [
                $farmer['id'],
                $farmer['names'],
                $farmer['phone_number'],
                $farmer['preferred_language']
            ]);
        }

        fclose($output);
        exit;

    case 'pdf':
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Title
        $pdf->Cell(190, 10, 'Registered Farmers List', 0, 1, 'C');
        $pdf->Ln(10);

        // Table Header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(20, 10, 'ID', 1);
        $pdf->Cell(80, 10, 'Name', 1);
        $pdf->Cell(50, 10, 'Phone Number', 1);
        $pdf->Cell(40, 10, 'Language', 1);
        $pdf->Ln();

        // Table Rows
        $pdf->SetFont('Arial', '', 10);
        foreach ($farmers as $farmer) {
            $pdf->Cell(20, 10, $farmer['id'], 1);
            $pdf->Cell(80, 10, $farmer['names'], 1);
            $pdf->Cell(50, 10, $farmer['phone_number'], 1);
            $pdf->Cell(40, 10, $farmer['preferred_language'], 1);
            $pdf->Ln();
        }

        $pdf->Output('D', 'farmers-export-' . date('Y-m-d') . '.pdf');
        exit;

    default:
        // Redirect if format is invalid
        header('Location: dashboard.php');
        exit;
}