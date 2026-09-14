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
        Schema::table('garment_items', function (Blueprint $table) {
            $table->decimal('standard_weight', 8, 2)->nullable()->default(null)->after('name')->comment('Poids standard en grammes (ex: 500 pour 500g)');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('pieces')->nullable()->default(1)->after('garment_item_id');
            $table->decimal('weight', 8, 3)->nullable()->default(null)->after('pieces')->comment('Poids en kg');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_weight', 8, 3)->nullable()->default(null)->after('total_amount')->comment('Poids total en kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_weight');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['pieces', 'weight']);
        });

        Schema::table('garment_items', function (Blueprint $table) {
            $table->dropColumn('standard_weight');
        });
    }
};
