<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('party_id')->nullable()->after('company_id')->constrained('parties')->nullOnDelete();
            $table->string('document_no')->nullable()->after('number');
            $table->date('date')->nullable()->after('document_no');
            $table->decimal('discount', 12, 2)->default(0)->after('status');
            $table->decimal('total', 12, 2)->default(0)->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['party_id']);
            $table->dropColumn(['party_id', 'document_no', 'date', 'discount', 'total']);
        });
    }
};
