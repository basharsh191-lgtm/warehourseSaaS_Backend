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
            $table->string('currency', 20)->nullable();
        });

        DB::table('companies')
            ->select(['id', 'currency'])
            ->orderBy('id')
            ->each(function (object $company): void {
                DB::table('movements')
                    ->where('company_id', $company->id)
                    ->update(['currency' => $company->currency]);
            });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
