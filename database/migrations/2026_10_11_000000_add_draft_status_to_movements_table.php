<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->string('status')->default('confirmed')->change();
        });
    }

    public function down(): void
    {
        DB::table('movements')->where('status', 'draft')->update(['status' => 'confirmed']);

        Schema::table('movements', function (Blueprint $table) {
            $table->enum('status', ['confirmed', 'cancelled'])->default('confirmed')->change();
        });
    }
};
