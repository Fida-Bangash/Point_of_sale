<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_ledger', function (Blueprint $table) {
            // How the money arrived (cash, bank, easypaisa, ...). Only set on `payment` entries.
            $table->string('payment_method', 20)->nullable();

            // Printed receipt number, e.g. PAY-20261126-0001. Only set on `payment` entries.
            $table->string('receipt_no', 30)->nullable()->index();

            // The customer's balance just before this entry was written. Stored so a reprinted
            // receipt always shows the same "previous / new balance" as the original one.
            $table->decimal('previous_balance', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('customer_ledger', function (Blueprint $table) {
            $table->dropIndex(['receipt_no']);
            $table->dropColumn(['payment_method', 'receipt_no', 'previous_balance']);
        });
    }
};
