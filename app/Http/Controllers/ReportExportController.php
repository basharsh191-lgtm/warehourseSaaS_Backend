<?php

namespace App\Http\Controllers;

use App\Models\ReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function index(Request $request): View
    {
        $reports = ReportExport::query()
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->paginate(20);

        return view('reports.index', compact('reports'));
    }

    public function download(Request $request, int $report): StreamedResponse
    {
        $reportExport = ReportExport::query()
            ->where('company_id', $request->user()->company_id)
            ->where('status', 'ready')
            ->findOrFail($report);

        abort_unless($reportExport->file_path && Storage::disk('local')->exists($reportExport->file_path), 404);

        return Storage::disk('local')->download($reportExport->file_path, "report-{$reportExport->id}.pdf");
    }
}
