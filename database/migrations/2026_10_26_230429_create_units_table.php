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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_name', 20)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    


    $now = now();
    DB::table('units')->insert(array_map(
        fn (array $u) => $u + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        [
        ['name' => 'Piece', 'short_name' => 'pcs'],
        ['name' => 'Kilogram', 'short_name' => 'kg'],
        ['name' => 'Gram', 'short_name' => 'g'],
        ['name' => 'Litre', 'short_name' => 'l'],
        ['name' => 'Millilitre', 'short_name' => 'ml'],
        ['name' => 'Dozen', 'short_name' => 'dz'],
        ['name' => 'Box', 'short_name' => 'box'],
        ['name' => 'Pack', 'short_name' => 'pack'],
        ['name' => 'Metre', 'short_name' => 'm'],
        ]
    ));
    }
    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
