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
        Schema::create('garment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garment_target_id')->nullable()->constrained('garment_targets')->nullOnDelete();
            $table->string('name'); // ex: Costume, Chemise, Pantalon
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garment_items');
    }
};
