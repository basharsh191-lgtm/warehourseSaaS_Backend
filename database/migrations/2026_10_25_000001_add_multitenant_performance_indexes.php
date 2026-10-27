<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addCompanyIndex('users', 'users_company_id_index');
        $this->addCompanyIndex('categories', 'categories_company_id_index');
        $this->addCompanyIndex('parties', 'parties_company_id_index');

        Schema::table('products', function (Blueprint $table) {
            $table->index(['company_id', 'barcode'], 'products_company_barcode_index');
            $table->index(['company_id', 'category_id'], 'products_company_category_index');
            $table->index(['company_id', 'is_active'], 'products_company_active_index');
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->index(['company_id', 'created_at'], 'movements_company_created_index');
            $table->index(['company_id', 'type'], 'movements_company_type_index');
            $table->index(['company_id', 'party_id'], 'movements_company_party_index');
            $table->index(['company_id', 'status'], 'movements_company_status_index');
            $table->index(['company_id', 'date'], 'movements_company_date_index');
        });

        Schema::table('operations', function (Blueprint $table) {
            $table->index(['company_id', 'created_at'], 'operations_company_created_index');
            $table->index(['company_id', 'type'], 'operations_company_type_index');
            $table->index(['company_id', 'party_id'], 'operations_company_party_index');
            $table->index(['company_id', 'date'], 'operations_company_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropIndex('operations_company_created_index');
            $table->dropIndex('operations_company_type_index');
            $table->dropIndex('operations_company_party_index');
            $table->dropIndex('operations_company_date_index');
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex('movements_company_created_index');
            $table->dropIndex('movements_company_type_index');
            $table->dropIndex('movements_company_party_index');
            $table->dropIndex('movements_company_status_index');
            $table->dropIndex('movements_company_date_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_company_barcode_index');
            $table->dropIndex('products_company_category_index');
            $table->dropIndex('products_company_active_index');
        });

        $this->dropIndexIfPresent('parties', 'parties_company_id_index');
        $this->dropIndexIfPresent('categories', 'categories_company_id_index');
        $this->dropIndexIfPresent('users', 'users_company_id_index');
    }

    private function addCompanyIndex(string $tableName, string $indexName): void
    {
        $hasCompanyIndex = collect(Schema::getIndexes($tableName))
            ->contains(fn (array $index) => ($index['columns'][0] ?? null) === 'company_id');

        if (! $hasCompanyIndex) {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->index('company_id', $indexName);
            });
        }
    }

    private function dropIndexIfPresent(string $tableName, string $indexName): void
    {
        $indexExists = collect(Schema::getIndexes($tableName))
            ->contains(fn (array $index) => $index['name'] === $indexName);

        if ($indexExists) {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};
