<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Part of the total that was NOT paid at the counter and went on the customer's account.
            $table->decimal('credit_amount', 12, 2)->default(0)->after('change_amount');

            // The customer's ledger balance just before this sale. Stored so a reprinted receipt
            // always shows the same "previous balance / new balance" as the original one.
            $table->decimal('previous_balance', 12, 2)->default(0)->after('credit_amount');
        });

        Schema::table('held_bills', function (Blueprint $table) {
            // Plain indexed column (no FK): SQLite cannot add constraints to an existing table.
            $table->unsignedBigInteger('customer_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('held_bills', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropColumn('customer_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['credit_amount', 'previous_balance']);
        });
    }
};
