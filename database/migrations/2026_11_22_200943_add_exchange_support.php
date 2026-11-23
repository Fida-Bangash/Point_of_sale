<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Part of a new sale's total that was paid with the value of goods handed back
        // (not cash). Cash actually received for a sale = total - exchange_credit.
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('exchange_credit', 12, 2)->default(0)->after('change_amount');
        });

        // One row ties together: old goods back (sale_return), new goods out (new sale) and the money difference.
        Schema::create('exchanges', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('exchange_no', 40)->unique();
            $table->foreignId('original_sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('sale_return_id')->nullable()->constrained('sale_returns')->nullOnDelete();
            $table->foreignId('new_sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('returned_value', 12, 2)->default(0); // worth of the goods handed back
            $table->decimal('new_value', 12, 2)->default(0);      // worth of the new goods (after discount)
            $table->decimal('credit_applied', 12, 2)->default(0); // min(returned_value, new_value)
            $table->decimal('cash_in', 12, 2)->default(0);        // customer paid the difference
            $table->decimal('cash_out', 12, 2)->default(0);       // shop paid the difference back
            $table->string('payment_method', 30)->default('cash');
            $table->string('note', 500)->nullable();
            $table->dateTime('exchanged_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchanges');

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('exchange_credit');
        });
    }
};