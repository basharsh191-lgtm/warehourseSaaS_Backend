<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('movement_id')->constrained('movements')->cascadeOnDelete();
            $table->foreignId('movement_item_id')->constrained('movement_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();

            $table->enum('type', ['purchase', 'sale', 'purchase_return', 'sale_return']);
            $table->date('date');

            $table->decimal('in_qty', 12, 3)->default(0.000);
            $table->decimal('out_qty', 12, 3)->default(0.000);
            $table->decimal('price_unit', 12, 2);
            $table->decimal('value', 12, 2); // قبل الحسم
            $table->decimal('value_net', 12, 2); // بعد الحسم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
