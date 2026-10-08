<?php

use App\Models\Companie;
use App\Models\Operation;
use App\Models\Partie;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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

test('purchase invoices increase stock and sale invoices cannot exceed available stock', function () {
    $product = Product::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'name' => 'صندوق',
        'barcode' => 'BOX-01',
        'unit' => 'قطعة',
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
        ->assertSee('BOX-01');

    $this->post(route('movements.store'), [
        'type' => 'purchase',
        'date' => now()->toDateString(),
        'discount' => '5.00',
        'document_no' => 'SUP-100',
        'items' => [
            ['product_id' => $product->id, 'quantity' => '2.500', 'price_unit' => '10.00'],
        ],
    ])->assertRedirect();

    $purchase = \App\Models\Movement::query()->firstOrFail();
    expect((float) $purchase->total)->toBe(20.0)
        ->and((float) Operation::query()->sum('in_qty'))->toBe(2.5);

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
