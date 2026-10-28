<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('related_movement_id')
                ->nullable()
                ->index('movements_related_movement_id_index')
                ->after('party_id')
                ->constrained('movements')
                ->nullOnDelete();
        });

        Schema::table('movement_items', function (Blueprint $table) {
            $table->foreignId('related_movement_item_id')
                ->nullable()
                ->index('movement_items_related_item_index')
                ->after('movement_id')
                ->constrained('movement_items')
                ->nullOnDelete();
            $table->decimal('total_net', 12, 2)->nullable()->after('total_line');
        });
    }

    public function down(): void
    {
        Schema::table('movement_items', function (Blueprint $table) {
            $table->dropForeign(['related_movement_item_id']);
            $table->dropIndex('movement_items_related_item_index');
            $table->dropColumn(['related_movement_item_id', 'total_net']);
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['related_movement_id']);
            $table->dropIndex('movements_related_movement_id_index');
            $table->dropColumn('related_movement_id');
        });
    }
};
