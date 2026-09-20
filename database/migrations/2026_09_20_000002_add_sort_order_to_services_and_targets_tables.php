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
        Schema::table('services', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('code');
        });

        Schema::table('garment_targets', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('name');
        });

        // Initialize sequential sort_order for existing records
        $services = DB::table('services')->orderBy('id')->get();
        $order = 1;
        foreach ($services as $s) {
            DB::table('services')->where('id', $s->id)->update(['sort_order' => $order++]);
        }

        $targets = DB::table('garment_targets')->orderBy('id')->get();
        $order = 1;
        foreach ($targets as $t) {
            DB::table('garment_targets')->where('id', $t->id)->update(['sort_order' => $order++]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garment_targets', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
