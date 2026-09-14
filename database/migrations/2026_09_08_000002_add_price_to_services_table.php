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
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->default(null)->after('code')->comment('Tarif global par défaut ou au kilo (détail)');
            $table->decimal('wholesale_price', 10, 2)->nullable()->default(null)->after('price')->comment('Tarif global par défaut ou au kilo (gros)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['price', 'wholesale_price']);
        });
    }
};
