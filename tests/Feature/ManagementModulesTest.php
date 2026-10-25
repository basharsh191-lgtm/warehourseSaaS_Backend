<?php

use App\Models\Companie;
use App\Models\Movement;
use App\Models\Operation;
use App\Models\Partie;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Companie::create([
        'name' => 'شركة الاختبار',
        'status' => 'trial',
        'trial_ends_at' => now()->addDays(14),
    ]);

    $this->user = User::create([
        'company_id' => $this->company->id,
        'name' => 'مدير الشركة',
        'email' => 'management@example.com',
        'password' => Hash::make('password-123'),
        'role' => 'company_admin',
        'is_active' => true,
    ]);

    $this->actingAs($this->user);
});

test('parties can be created, edited, and deactivated without deletion', function () {
    $this->get(route('parties.index'))->assertOk()->assertSee('قائمة الأطراف');

    $this->post(route('parties.store'), [
        'name' => 'عميل تجريبي',
        'phone' => '0500000000',
        'type' => 'customer',
    ])->assertRedirectToRoute('parties.index');

    $party = Partie::query()->where('name', 'عميل تجريبي')->firstOrFail();

    $this->put(route('parties.update', $party), [
        'name' => 'عميل محدث',
        'phone' => null,
        'type' => 'supplier',
    ])->assertRedirectToRoute('parties.index');
    $this->patch(route('parties.toggle-status', $party))->assertRedirectToRoute('parties.index');

    expect($party->fresh()->name)->toBe('عميل محدث')
        ->and($party->fresh()->type)->toBe('supplier')
        ->and($party->fresh()->is_active)->toBeFalse();
});

test('party phone numbers are unique per type and can be retained on update', function () {
    $customer = Partie::create([
        'company_id' => $this->company->id,
        'name' => 'العميل الأول',
        'phone' => '0501111111',
        'type' => 'customer',
        'is_active' => true,
    ]);

    $this->from(route('parties.index'))
        ->post(route('parties.store'), [
            'name' => 'عميل آخر',
            'phone' => '0501111111',
            'type' => 'customer',
        ])
        ->assertRedirect(route('parties.index'))
        ->assertSessionHasErrors([
            'phone' => 'رقم الهاتف هذا مستخدم بالفعل لطرف آخر',
        ]);

    $this->put(route('parties.update', $customer), [
        'name' => 'العميل الأول',
        'phone' => '0501111111',
        'type' => 'customer',
    ])->assertRedirectToRoute('parties.index');

    $this->post(route('parties.store'), [
        'name' => 'المورد الأول',
        'phone' => '0501111111',
        'type' => 'supplier',
    ])->assertRedirectToRoute('parties.index');

    expect(fn () => Partie::create([
        'company_id' => $this->company->id,
        'name' => 'عميل مكرر من قاعدة البيانات',
        'phone' => '0501111111',
        'type' => 'customer',
        'is_active' => true,
    ]))->toThrow(QueryException::class);
});

test('pricing markup is validated and saved for the current company', function () {
    $this->get(route('settings.pricing.edit'))->assertOk()->assertSee('name="percent_markup"', false);

    $this->put(route('settings.pricing.update'), ['percent_markup' => '275.50'])
        ->assertRedirectToRoute('settings.pricing.edit');

    expect((float) $this->company->fresh()->percent_markup)->toBe(275.5);

    $this->from(route('settings.pricing.edit'))
        ->put(route('settings.pricing.update'), ['percent_markup' => '-1'])
        ->assertRedirect(route('settings.pricing.edit'))
        ->assertSessionHasErrors('percent_markup');
});

test('company settings update contact details and store a company logo', function () {
    Storage::fake('public');

    $this->get(route('settings.index', ['tab' => 'company']))
        ->assertOk()
        ->assertSee('بيانات الشركة')
        ->assertSee('name="logo"', false);

    $logo = UploadedFile::fake()->createWithContent(
        'company-logo.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
    );

    $this->put(route('settings.company.update', ['tab' => 'company']), [
        'name' => 'شركة محدثة',
        'phone' => '0501234567',
        'email' => 'office@example.com',
        'address' => 'الرياض، المملكة العربية السعودية',
        'currency' => 'SAR',
        'logo' => $logo,
    ])->assertRedirect(route('settings.index', ['tab' => 'company']));

    $this->from(route('settings.index', ['tab' => 'company']))
        ->put(route('settings.company.update', ['tab' => 'company']), [
            'name' => 'شركة محدثة',
            'currency' => 'INVALID',
        ])
        ->assertRedirect(route('settings.index', ['tab' => 'company']))
        ->assertSessionHasErrors('currency');

    $company = $this->company->fresh();
    expect($company->name)->toBe('شركة محدثة')
        ->and($company->phone)->toBe('0501234567')
        ->and($company->email)->toBe('office@example.com')
        ->and($company->address)->toBe('الرياض، المملكة العربية السعودية')
        ->and($company->currency)->toBe('SAR')
        ->and($company->logo_path)->not->toBeNull();

    Storage::disk('public')->assertExists($company->logo_path);
});

test('saved company logo uses the current application host in company navigation and settings', function () {
    Storage::fake('public');
    Storage::disk('public')->put('company-logos/company-logo.png', 'logo');
    $this->company->update(['logo_path' => 'company-logos/company-logo.png']);

    $this->get(route('settings.index', ['tab' => 'company']))
        ->assertOk()
        ->assertSee('src="'.asset('storage/company-logos/company-logo.png').'"', false)
        ->assertSee('alt="الشعار الحالي للشركة"', false);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('src="'.asset('storage/company-logos/company-logo.png').'"', false)
        ->assertSee('alt="شعار '.$this->company->name.'"', false);
});

test('purchase invoices increase stock and sale invoices cannot exceed available stock', function () {
    $product = Product::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'name' => 'صندوق',
        'barcode' => 'BOX-01',
        'unit' => 'قطعة',
        'item_shape' => 'قياسي',
        'price_sale_custom' => null,
        'is_active' => true,
    ]);
    Partie::create([
        'company_id' => $this->company->id,
        'name' => 'المورد الأول',
        'type' => 'supplier',
        'is_active' => true,
    ]);
    $customer = Partie::create([
        'company_id' => $this->company->id,
        'name' => 'العميل الأول',
        'type' => 'customer',
        'is_active' => true,
    ]);

    $this->get(route('movements.create'))
        ->assertOk()
        ->assertSee("'items['", false)
        ->assertSee('شكل المادة / الموديل')
        ->assertSee('BOX-01');

    $this->post(route('movements.store'), [
        'type' => 'purchase',
        'date' => now()->toDateString(),
        'discount' => '5.00',
        'document_no' => 'SUP-100',
        'items' => [
            ['product_id' => $product->id, 'quantity' => '2.500', 'price_unit' => '10.00', 'item_shape' => 'شكل مخصص للفاتورة'],
        ],
    ])->assertRedirect();

    $purchase = \App\Models\Movement::query()->firstOrFail();
    expect((float) $purchase->total)->toBe(20.0)
        ->and((float) $purchase->discount_value)->toBe(5.0)
        ->and((float) $purchase->discount_amount)->toBe(5.0)
        ->and($purchase->items()->firstOrFail()->item_shape)->toBe('شكل مخصص للفاتورة')
        ->and((float) Operation::query()->sum('in_qty'))->toBe(2.5);
    $this->get(route('movements.show', $purchase))
        ->assertOk()
        ->assertSee('شكل مخصص للفاتورة');

    $this->post(route('movements.store'), [
        'type' => 'sale',
        'party_id' => $customer->id,
        'date' => now()->toDateString(),
        'discount' => '0',
        'items' => [
            ['product_id' => $product->id, 'quantity' => '1.250', 'price_unit' => '30.00'],
        ],
    ])->assertRedirect();

    $this->from(route('movements.create'))
        ->post(route('movements.store'), [
            'type' => 'sale',
            'date' => now()->toDateString(),
            'discount' => '0',
            'items' => [
                ['product_id' => $product->id, 'quantity' => '1.500', 'price_unit' => '30.00'],
            ],
        ])
        ->assertRedirect(route('movements.create'))
        ->assertSessionHasErrors('items');

    expect((float) Operation::query()->sum('in_qty') - (float) Operation::query()->sum('out_qty'))->toBe(1.25);
});

test('percentage invoice discounts are calculated and persisted separately from their input value', function () {
    $product = Product::create([
        'company_id' => $this->company->id,
        'name' => 'مادة الخصم النسبي',
        'unit' => 'قطعة',
        'price_sale_custom' => null,
        'is_active' => true,
    ]);
    $this->post(route('movements.store'), [
        'type' => 'purchase',
        'date' => now()->toDateString(),
        'discount_value' => 0,
        'items' => [['product_id' => $product->id, 'quantity' => 10, 'price_unit' => 10]],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->post(route('movements.store'), [
        'type' => 'sale',
        'date' => now()->toDateString(),
        'discount_type' => 'percentage',
        'discount_value' => '12.5',
        'items' => [['product_id' => $product->id, 'quantity' => 2, 'price_unit' => 20]],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $sale = Movement::query()->where('type', 'sale')->firstOrFail();
    expect($sale->discount_type)->toBe('percentage')
        ->and((float) $sale->discount_value)->toBe(12.5)
        ->and((float) $sale->discount_amount)->toBe(5.0)
        ->and((float) $sale->discount)->toBe(5.0)
        ->and((float) $sale->total)->toBe(35.0);

    $this->get(route('movements.show', $sale))
        ->assertOk()
        ->assertSee('12.50%')
        ->assertSee('35.00 USD');

    $this->from(route('movements.create'))
        ->post(route('movements.store'), [
            'type' => 'sale',
            'date' => now()->toDateString(),
            'discount_type' => 'percentage',
            'discount_value' => 100.01,
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'price_unit' => 20]],
        ])
        ->assertRedirect(route('movements.create'))
        ->assertSessionHasErrors('discount_value');
});

test('invoice currency can be entered manually without changing the company default', function () {
    $product = Product::create([
        'company_id' => $this->company->id,
        'name' => 'مادة بعملة مخصصة',
        'unit' => 'قطعة',
        'price_sale_custom' => null,
        'is_active' => true,
    ]);

    $this->get(route('movements.create'))
        ->assertOk()
        ->assertSee('name="currency"', false)
        ->assertSee('كتابة يدوية');

    $this->post(route('movements.store'), [
        'type' => 'purchase',
        'date' => now()->toDateString(),
        'currency' => 'د.ك',
        'discount_value' => 0,
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'price_unit' => 10]],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $movement = Movement::query()->firstOrFail();
    expect($movement->currency)->toBe('د.ك')
        ->and($this->company->fresh()->currency)->toBe('USD');

    $this->get(route('movements.show', $movement))
        ->assertOk()
        ->assertSee('10.00 د.ك');
});

test('draft invoices do not change stock until confirmed', function () {
    $product = Product::create([
        'company_id' => $this->company->id,
        'name' => 'مخزون المسودات',
        'barcode' => 'DRAFT-01',
        'unit' => 'قطعة',
        'price_sale_custom' => null,
        'is_active' => true,
    ]);

    $this->post(route('movements.store'), [
        'type' => 'purchase',
        'date' => now()->toDateString(),
        'discount' => 0,
        'items' => [['product_id' => $product->id, 'quantity' => 2, 'price_unit' => 10]],
    ])->assertRedirect();

    $this->post(route('movements.store'), [
        'type' => 'sale',
        'status' => 'draft',
        'date' => now()->toDateString(),
        'discount' => 0,
        'items' => [['product_id' => $product->id, 'quantity' => 3, 'price_unit' => 20]],
    ])->assertRedirect();

    $draft = Movement::query()->where('status', 'draft')->firstOrFail();
    expect((float) Operation::query()->where('product_id', $product->id)->sum('in_qty'))->toBe(2.0)
        ->and((float) Operation::query()->where('product_id', $product->id)->sum('out_qty'))->toBe(0.0)
        ->and($draft->items)->toHaveCount(1);

    $this->from(route('movements.show', $draft))
        ->post(route('movements.confirm', $draft))
        ->assertRedirect(route('movements.show', $draft))
        ->assertSessionHasErrors('items');

    $this->post(route('movements.store'), [
        'type' => 'purchase',
        'date' => now()->toDateString(),
        'discount' => 0,
        'items' => [['product_id' => $product->id, 'quantity' => 2, 'price_unit' => 10]],
    ])->assertRedirect();

    $this->post(route('movements.confirm', $draft))
        ->assertRedirectToRoute('movements.show', $draft)
        ->assertSessionHasNoErrors();

    expect($draft->fresh()->status)->toBe('confirmed')
        ->and((float) Operation::query()->where('product_id', $product->id)->sum('in_qty') - (float) Operation::query()->where('product_id', $product->id)->sum('out_qty'))->toBe(1.0)
        ->and(Operation::query()->where('movement_id', $draft->id)->count())->toBe(1);
});

test('invoice filters and PDF exports use the same company-scoped criteria', function () {
    $customer = Partie::create([
        'company_id' => $this->company->id,
        'name' => 'عميل التقرير',
        'type' => 'customer',
        'is_active' => true,
    ]);
    $matching = Movement::create([
        'company_id' => $this->company->id,
        'party_id' => $customer->id,
        'type' => 'sale',
        'number' => 501,
        'document_no' => 'INV-501',
        'date' => '2026-10-10',
        'status' => 'draft',
        'discount' => 0,
        'total' => 100,
    ]);
    Movement::create([
        'company_id' => $this->company->id,
        'type' => 'purchase',
        'number' => 502,
        'document_no' => 'INV-502',
        'date' => '2026-10-01',
        'status' => 'confirmed',
        'discount' => 0,
        'total' => 50,
    ]);
    $otherCompany = Companie::create(['name' => 'شركة أخرى', 'status' => 'active']);
    Movement::create([
        'company_id' => $otherCompany->id,
        'type' => 'sale',
        'number' => 503,
        'document_no' => 'INV-503',
        'date' => '2026-10-10',
        'status' => 'draft',
        'discount' => 0,
        'total' => 25,
    ]);

    $filters = [
        'type' => 'sale',
        'status' => 'draft',
        'party_id' => $customer->id,
        'from_date' => '2026-10-09',
        'to_date' => '2026-10-11',
        'search' => 'INV-501',
    ];
    $this->get(route('movements.index', $filters))
        ->assertOk()
        ->assertSee('#501')
        ->assertDontSee('#502')
        ->assertDontSee('#503');

    $report = $this->get(route('movements.export.pdf', $filters))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'attachment; filename="invoices-report.pdf"');
    $this->assertStringStartsWith('%PDF', $report->getContent());

    $invoice = $this->get(route('movements.pdf', $matching))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename="invoice-501.pdf"');
    $this->assertStringStartsWith('%PDF', $invoice->getContent());
});
