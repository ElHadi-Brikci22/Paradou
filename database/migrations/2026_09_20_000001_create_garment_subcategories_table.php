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
        Schema::create('garment_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garment_target_id')->constrained('garment_targets')->cascadeOnDelete();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('garment_items', function (Blueprint $table) {
            $table->foreignId('garment_subcategory_id')->nullable()->after('garment_target_id')->constrained('garment_subcategories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garment_items', function (Blueprint $table) {
            $table->dropForeign(['garment_subcategory_id']);
            $table->dropColumn('garment_subcategory_id');
        });

        Schema::dropIfExists('garment_subcategories');
    }
};
