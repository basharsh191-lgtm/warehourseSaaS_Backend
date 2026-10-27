<?php

namespace App\Services;

use App\Models\Movement;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use TCPDF;

class MovementPdfService
{
    public function invoice(Movement $movement): Response
    {
        $html = view('pdf.movements.invoice', compact('movement'))->render();

        return $this->makePdf($html, "invoice-{$movement->number}.pdf", 'inline');
    }

    public function reportContent(Collection $movements, string $currency): string
    {
        $html = view('pdf.movements.report', compact('movements', 'currency'))->render();

        return $this->makePdfContent($html, 'invoices-report.pdf');
    }

    private function makePdf(string $html, string $filename, string $disposition): \Illuminate\Http\Response
    {
        return response($this->makePdfContent($html, $filename), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
        ]);
    }

    private function makePdfContent(string $html, string $filename): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(config('app.name'));
        $pdf->SetTitle(pathinfo($filename, PATHINFO_FILENAME));
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setRTL(true);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output($filename, 'S');
    }
}
