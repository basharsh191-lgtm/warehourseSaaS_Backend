<?php

namespace App\Jobs;

use App\Models\Companie;
use App\Models\ReportExport;
use App\Services\MovementPdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GenerateReportPdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(public int $reportExportId) {}

    public function handle(MovementPdfService $pdfService): void
    {
        $report = ReportExport::query()->findOrFail($this->reportExportId);
        $report->update(['status' => 'processing', 'error' => null]);

        $company = Companie::query()->findOrFail($report->company_id);
        $filters = $report->filters ?? [];

        $movements = $company->movements()
            ->with(['party', 'company'])
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['party_id'] ?? null, fn ($query, $partyId) => $query->where('party_id', $partyId))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date))
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('date', '<=', $date))
            ->when(
                isset($filters['search']) && $filters['search'] !== '',
                function ($query) use ($filters) {
                    $search = trim($filters['search']);
                    $query->where(function ($query) use ($search) {
                        $query->where('number', 'like', "%{$search}%")
                            ->orWhere('document_no', 'like', "%{$search}%");
                    });
                }
            )
            ->latest('date')
            ->latest('number')
            ->get();

        $path = "reports/{$report->id}.pdf";
        $stored = Storage::disk('local')->put(
            $path,
            $pdfService->reportContent($movements, $company->currency)
        );

        if (! $stored) {
            throw new RuntimeException("Unable to store PDF report {$report->id}.");
        }

        $report->update([
            'status' => 'ready',
            'file_path' => $path,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        ReportExport::query()
            ->whereKey($this->reportExportId)
            ->update([
                'status' => 'failed',
                'error' => 'تعذر إنشاء التقرير. يرجى إعادة المحاولة أو التواصل مع الدعم.',
            ]);
    }
}
