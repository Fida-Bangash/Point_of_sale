<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            // Part of the refund that was NOT paid out but taken off the customer's account (khata).
            $table->decimal('ledger_amount', 12, 2)->default(0)->after('refund_total');

            // Part of the refund that paid for new goods in an exchange (the cash difference of an
            // exchange is stored on the exchange itself).
            $table->decimal('exchange_amount', 12, 2)->default(0)->after('ledger_amount');
        });

        // Old exchange returns: the whole refund was "exchange credit" back then
        DB::table('sale_returns')->where('refund_method', 'exchange')->update(['exchange_amount' => DB::raw('refund_total')]);

        Schema::table('exchanges', function (Blueprint $table) {
            // cash_in / cash_out are now REAL money. These two are the same difference, but settled
            // on the customer's account instead of at the counter.
            $table->decimal('ledger_in', 12, 2)->default(0)->after('cash_out');   // customer owes more (udhaar)
            $table->decimal('ledger_out', 12, 2)->default(0)->after('ledger_in'); // customer owes less (credit)
        });
    }

    public function down(): void
    {
        Schema::table('exchanges', function (Blueprint $table) {
            $table->dropColumn(['ledger_in', 'ledger_out']);
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropColumn(['ledger_amount', 'exchange_amount']);
        });
    }
};
