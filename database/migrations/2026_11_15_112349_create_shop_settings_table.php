<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name')->default('My Shop');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Pakistan');
            $table->string('tax_number', 50)->nullable();
            $table->string('logo')->nullable();
            $table->string('currency_symbol', 10)->default('Rs');
            $table->string('currency_code', 3)->default('PKR');
            $table->string('receipt_footer', 500)->nullable();
            $table->timestamps();
        });

        
        DB::table('shop_settings')->insert([
            'id'              => 1,
            'shop_name'       => 'My Shop',
            'country'         => 'Pakistan',
            'currency_symbol' => 'Rs',
            'currency_code'   => 'PKR',
            'receipt_footer'  => 'Thank you for shopping with us!',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};
