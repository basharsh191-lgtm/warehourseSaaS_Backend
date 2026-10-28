<?php

use App\Models\Categorie;
use App\Models\Companie;
use App\Models\Movement;
use App\Models\MovementItems;
use App\Models\Operation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Companie::create([
        'name' => 'شركة المخزون',
        'status' => 'trial',
        'trial_ends_at' => now()->addDays(14),
    ]);

    $this->user = User::create([
        'company_id' => $this->company->id,
        'name' => 'مسؤول المخزون',
        'email' => 'catalog@example.com',
        'password' => Hash::make('password-123'),
        'role' => 'company_admin',
        'is_active' => true,
    ]);
});

test('trial companies can open the products list', function () {
    $this->actingAs($this->user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('قائمة المواد');
});

test('suspended companies are redirected to the suspension page', function () {
    $this->company->update(['status' => 'suspended']);

    $this->actingAs($this->user)
        ->get(route('products.index'))
        ->assertRedirectToRoute('subscription.suspended')
        ->assertSessionHas('error', 'حساب الشركة موقوف، يرجى التواصل مع الدعم');

    $this->get(route('subscription.suspended'))
        ->assertOk()
        ->assertSee('حساب الشركة موقوف، يرجى التواصل مع الدعم');
});

test('catalog global scopes restrict products and categories to the current company', function () {
    $ownCategory = Categorie::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'name' => 'مواد شركتي',
        'is_active' => true,
    ]);
    Product::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'category_id' => $ownCategory->id,
        'name' => 'مادة شركتي',
        'unit' => 'قطعة',
    ]);

    $otherCompany = Companie::create(['name' => 'شركة أخرى', 'status' => 'active']);
    $otherCategory = Categorie::withoutGlobalScopes()->create([
        'company_id' => $otherCompany->id,
        'name' => 'فئة شركة أخرى',
        'is_active' => true,
    ]);
    Product::withoutGlobalScopes()->create([
        'company_id' => $otherCompany->id,
        'category_id' => $otherCategory->id,
        'name' => 'مادة شركة أخرى',
        'unit' => 'قطعة',
    ]);

    $this->actingAs($this->user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('مادة شركتي')
        ->assertDontSee('مادة شركة أخرى');

    expect(Categorie::query()->pluck('name')->all())->toBe(['مواد شركتي']);
    expect(Product::query()->pluck('name')->all())->toBe(['مادة شركتي']);
});

test('product validation prevents using another company category', function () {
    $otherCompany = Companie::create(['name' => 'شركة أخرى', 'status' => 'active']);
    $foreignCategory = Categorie::withoutGlobalScopes()->create([
        'company_id' => $otherCompany->id,
        'name' => 'فئة خاصة',
        'is_active' => true,
    ]);

    $this->actingAs($this->user)
        ->from(route('products.create'))
        ->post(route('products.store'), [
            'name' => 'مادة غير صالحة',
            'unit' => 'قطعة',
            'category_id' => $foreignCategory->id,
        ])
        ->assertRedirect(route('products.create'))
        ->assertSessionHasErrors('category_id');

    expect(Product::withoutGlobalScopes()->where('name', 'مادة غير صالحة')->exists())->toBeFalse();
});

test('category can be added and deactivated without deleting it', function () {
    $this->actingAs($this->user)
        ->post(route('categories.store'), [
            'name' => 'مشروبات',
            'description' => 'مشروبات متنوعة',
        ])
        ->assertRedirectToRoute('categories.index');

    $category = Categorie::query()->where('name', 'مشروبات')->firstOrFail();

    $this->patch(route('categories.toggle-status', $category))
        ->assertRedirectToRoute('categories.index');

    expect(Categorie::query()->findOrFail($category->id)->is_active)->toBeFalse();
});

test('product can be saved with inventory details and deactivated', function () {
    $category = Categorie::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'name' => 'أدوات',
        'is_active' => true,
    ]);

    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'barcode' => '1234567890123',
            'name' => 'صندوق تخزين',
            'unit' => 'قطعة',
            'item_shape' => 'مستطيل',
            'location' => 'A-03',
            'price_sale_custom' => '12.50',
            'stock_min' => '2.500',
            'category_id' => $category->id,
        ])
        ->assertRedirectToRoute('products.index');

    $product = Product::query()->where('barcode', '1234567890123')->firstOrFail();
    expect($product->name)->toBe('صندوق تخزين')
        ->and($product->item_shape)->toBe('مستطيل')
        ->and($product->location)->toBe('A-03')
        ->and((float) $product->price_sale_custom)->toBe(12.5)
        ->and((float) $product->stock_min)->toBe(2.5);

    $this->put(route('products.update', $product), [
        'barcode' => '1234567890123',
        'name' => 'صندوق تخزين',
        'unit' => 'قطعة',
        'item_shape' => 'دائري',
        'location' => 'A-03',
        'price_sale_custom' => '12.50',
        'stock_min' => '2.500',
        'category_id' => $category->id,
    ])->assertRedirectToRoute('products.index');

    expect($product->fresh()->item_shape)->toBe('دائري');

    $this->patch(route('products.toggle-status', $product))
        ->assertRedirectToRoute('products.index');

    expect(Product::query()->findOrFail($product->id)->is_active)->toBeFalse();
});

test('category and product forms render with validation fields', function () {
    $this->actingAs($this->user)
        ->get(route('categories.create'))
        ->assertOk()
        ->assertSee('name="description"', false);

    $this->get(route('products.create'))
        ->assertOk()
        ->assertSee('name="barcode"', false)
        ->assertSee('name="name"', false)
        ->assertSee('name="unit"', false)
        ->assertSee('name="location"', false)
        ->assertSee('name="price_sale_custom"', false)
        ->assertSee('name="stock_min"', false);
});

test('company administrators cannot access another company product by route binding', function () {
    $otherCompany = Companie::create(['name' => 'شركة أخرى', 'status' => 'active']);
    $foreignProduct = Product::withoutGlobalScopes()->create([
        'company_id' => $otherCompany->id,
        'name' => 'مادة سرية',
        'unit' => 'قطعة',
    ]);

    $this->actingAs($this->user)
        ->get(route('products.edit', $foreignProduct))
        ->assertNotFound();
});

test('products page shows low-stock warning for the current company only', function () {
    Product::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'name' => 'مادة منخفضة المخزون',
        'unit' => 'قطعة',
        'stock_min' => 5,
        'is_active' => true,
    ]);
    $healthyProduct = Product::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'name' => 'مادة مخزونها كافٍ',
        'unit' => 'قطعة',
        'stock_min' => 1,
        'is_active' => true,
    ]);

    $otherCompany = Companie::create(['name' => 'شركة خارج النطاق', 'status' => 'active']);
    Product::withoutGlobalScopes()->create([
        'company_id' => $otherCompany->id,
        'name' => 'مادة شركة أخرى',
        'unit' => 'قطعة',
        'stock_min' => 5,
        'is_active' => true,
    ]);

    $movement = Movement::query()->create([
        'company_id' => $this->company->id,
        'type' => 'purchase',
        'number' => 1,
        'date' => today(),
        'status' => 'confirmed',
        'currency' => 'USD',
        'total' => 2,
    ]);
    $item = MovementItems::query()->create([
        'movement_id' => $movement->id,
        'product_id' => $healthyProduct->id,
        'quantity' => 2,
        'price_unit' => 1,
        'total_line' => 2,
    ]);
    Operation::query()->create([
        'company_id' => $this->company->id,
        'movement_id' => $movement->id,
        'movement_item_id' => $item->id,
        'product_id' => $healthyProduct->id,
        'type' => 'purchase',
        'date' => today(),
        'in_qty' => 2,
        'out_qty' => 0,
        'price_unit' => 1,
        'value' => 2,
        'value_net' => 2,
    ]);

    $this->actingAs($this->user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('مواد عند الحد الأدنى أو أقل: 1')
        ->assertSee('مادة منخفضة المخزون')
        ->assertSee('منخفض')
        ->assertDontSee('مادة شركة أخرى');

    $this->get(route('products.index', ['low_stock' => 1]))
        ->assertOk()
        ->assertSee('مادة منخفضة المخزون')
        ->assertDontSee('مادة مخزونها كافٍ');
});
