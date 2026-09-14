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
        Schema::table('garment_items', function (Blueprint $table) {
            $table->boolean('is_carpet')->default(false)->after('unit_type')->comment('Indique si l\'article est un tapis (facturé au m²)');
        });

        // Synchroniser les articles existants
        DB::table('garment_items')
            ->where('unit_type', 'm2')
            ->orWhere('name', 'LIKE', '%tapis%')
            ->orWhere('name', 'LIKE', '%m²%')
            ->orWhere('name', 'LIKE', '%m2%')
            ->update([
                'is_carpet' => true,
                'unit_type' => 'm2',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garment_items', function (Blueprint $table) {
            $table->dropColumn('is_carpet');
        });
    }
};
