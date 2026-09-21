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
            $table->integer('pieces_count')->default(1)->after('is_carpet')->comment('Nombre de pièces physiques composant l\'article');
        });

        // 1. Articles à 3 pièces
        DB::table('garment_items')
            ->where('name', 'LIKE', '%3 pièces%')
            ->orWhere('name', 'LIKE', '%3 pieces%')
            ->orWhere('name', 'LIKE', '%3 pcs%')
            ->orWhere('name', 'LIKE', '%3ps%')
            ->update(['pieces_count' => 3]);

        // 2. Articles à 2 pièces
        $twoPieceItems = DB::table('garment_items')->get();
        foreach ($twoPieceItems as $item) {
            $nameLower = mb_strtolower($item->name);
            $pieces = 1;

            if (preg_match('/3\s*(pièces?|pieces?|pcs?|ps)/iu', $item->name)) {
                $pieces = 3;
            } elseif (preg_match('/2\s*(pièces?|pieces?|pcs?|ps)/iu', $item->name)) {
                $pieces = 2;
            } elseif (str_contains($item->name, '+')) {
                $pieces = count(explode('+', $item->name));
            } elseif (str_contains($nameLower, 'tailleur') && !str_contains($nameLower, 'seul')) {
                $pieces = 2;
            } elseif ($nameLower === 'smoking') {
                $pieces = 2;
            } elseif (str_contains($nameLower, 'costume') && !str_contains($nameLower, 'seul') && !str_contains($nameLower, 'veste') && !str_contains($nameLower, 'pantalon') && !str_contains($nameLower, 'gilet') && !str_contains($nameLower, 'jupe')) {
                $pieces = 2;
            } elseif (str_contains($nameLower, 'survêtement') || str_contains($nameLower, 'survetement')) {
                $pieces = 2;
            } elseif (str_contains($nameLower, 'ensemble') && !str_contains($nameLower, 'seul') && !str_contains($nameLower, 'travail')) {
                $pieces = 2;
            }

            if ($pieces > 1) {
                DB::table('garment_items')->where('id', $item->id)->update(['pieces_count' => $pieces]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garment_items', function (Blueprint $table) {
            $table->dropColumn('pieces_count');
        });
    }
};
