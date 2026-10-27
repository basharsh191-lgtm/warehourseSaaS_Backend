<?php

use App\Jobs\GenerateReportPdfJob;
use App\Models\Categorie;
use App\Models\Companie;
use App\Models\Operation;
use App\Models\Product;
use App\Models\ReportExport;
use App\Models\User;
use App\Services\MovementPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Companie::query()->create([
        'name' => 'شركة البيانات',
        'status' => 'active',
    ]);
    $this->user = User::query()->create([
        'company_id' => $this->company->id,
        'name' => 'مدير الشركة',
        'email' => 'data-admin@example.test',
        'password' => 'password',
        'role' => 'company_admin',
        'is_active' => true,
    ]);
    $this->actingAs($this->user);
});

test('product import skips duplicate barcodes and records opening stock', function () {
    $category = Categorie::query()->create([
        'company_id' => $this->company->id,
        'name' => 'أجهزة',
        'is_active' => true,
    ]);
    Product::query()->create([
        'company_id' => $this->company->id,
        'name' => 'منتج سابق',
        'barcode' => 'EXISTING',
        'unit' => 'قطعة',
    ]);

    $csv = "name,barcode,category,unit,price,initial_quantity\n"
        ."مكرر,EXISTING,أجهزة,قطعة,10,2\n"
        ."منتج جديد,NEW-1,أجهزة,قطعة,5.50,3\n"
        ."تكرار داخل الملف,NEW-1,أجهزة,قطعة,7,1\n";

    $this->post(route('products.import'), [
        'file' => UploadedFile::fake()->createWithContent('products.csv', $csv),
    ])
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success', 'تم استيراد 1 مادة وتخطي 2 صف.')
        ->assertSessionHas('import_errors.0.row', 2)
        ->assertSessionHas('import_errors.1.row', 4);

    $product = Product::query()->where('barcode', 'NEW-1')->firstOrFail();
    expect($product->company_id)->toBe($this->company->id)
        ->and($product->category_id)->toBe($category->id)
        ->and((float) $product->price_sale_custom)->toBe(5.5);

    $operation = Operation::query()->where('product_id', $product->id)->firstOrFail();
    expect((float) $operation->in_qty)->toBe(3.0)
        ->and((float) $operation->out_qty)->toBe(0.0);
});

test('product import rejects unsupported spreadsheets', function () {
    $this->post(route('products.import'), [
        'file' => UploadedFile::fake()->createWithContent('products.xls', 'not supported'),
    ])->assertSessionHasErrors('file');
});

test('product and movement exports return spreadsheet downloads', function () {
    $productExport = $this->get(route('products.export.excel'))
        ->assertOk()
        ->assertHeader('content-disposition');
    expect($productExport->streamedContent())->toStartWith('PK');

    $movementExport = $this->get(route('movements.export.excel'))
        ->assertOk()
        ->assertHeader('content-disposition');
    expect($movementExport->streamedContent())->toStartWith('PK');
});

test('pdf report requests are queued and restricted to their company', function () {
    Queue::fake();

    $this->post(route('movements.export.pdf'))
        ->assertRedirect(route('reports.index'));

    $report = ReportExport::query()->firstOrFail();
    expect($report->status)->toBe('pending')
        ->and($report->company_id)->toBe($this->company->id);
    Queue::assertPushed(GenerateReportPdfJob::class);
    $this->get(route('reports.index'))->assertOk()->assertSee('بانتظار المعالجة');

    $otherCompany = Companie::query()->create(['name' => 'شركة أخرى', 'status' => 'active']);
    $otherReport = ReportExport::query()->create([
        'company_id' => $otherCompany->id,
        'user_id' => $this->user->id,
        'status' => 'ready',
        'filters' => [],
        'file_path' => 'reports/private.pdf',
    ]);

    $this->get(route('reports.download', $otherReport))->assertNotFound();
});

test('queued report job stores its PDF and marks the report ready', function () {
    Storage::fake('local');
    $this->company->movements()->create([
        'type' => 'purchase',
        'number' => 1,
        'document_no' => 'OPEN-1',
        'date' => today(),
        'status' => 'confirmed',
        'currency' => 'USD',
        'total' => 0,
    ]);
    $report = ReportExport::query()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'status' => 'pending',
        'filters' => [],
    ]);

    (new GenerateReportPdfJob($report->id))->handle(app(MovementPdfService::class));

    expect($report->fresh()->status)->toBe('ready')
        ->and(Storage::disk('local')->exists("reports/{$report->id}.pdf"))->toBeTrue();

    $this->get(route('reports.download', $report))->assertOk();
});
