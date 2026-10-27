<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->json('filters');
            $table->string('file_path')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status', 'created_at'], 'report_exports_company_status_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
