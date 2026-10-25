<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('currency', 10)->default('USD');
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->string('discount_type', 20)->default('fixed');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
        });

        DB::table('movements')->update([
            'discount_value' => DB::raw('discount'),
            'discount_amount' => DB::raw('discount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'discount_amount']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
