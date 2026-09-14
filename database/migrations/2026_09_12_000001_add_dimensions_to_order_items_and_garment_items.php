<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('length', 8, 2)->nullable()->default(null)->after('weight')->comment('Longueur en mètres');
            $table->decimal('width', 8, 2)->nullable()->default(null)->after('length')->comment('Largeur en mètres');
            $table->decimal('area', 8, 2)->nullable()->default(null)->after('width')->comment('Surface en m²');
            $table->boolean('is_measured')->default(false)->after('area')->comment('Indique si le tapis a été mesuré');
        });

        Schema::table('garment_items', function (Blueprint $table) {
            $table->string('unit_type', 20)->default('piece')->after('standard_weight')->comment('Type d\'unité: piece, kg, m2');
        });

        // Set unit_type = 'm2' for any existing items containing tapis or m²
        DB::table('garment_items')
            ->where('name', 'LIKE', '%tapis%')
            ->orWhere('name', 'LIKE', '%m²%')
            ->orWhere('name', 'LIKE', '%m2%')
            ->update(['unit_type' => 'm2']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garment_items', function (Blueprint $table) {
            $table->dropColumn('unit_type');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'area', 'is_measured']);
        });
    }
};
