<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Plain indexed column (no FK constraint): SQLite cannot add constraints to an existing table.
            // customer_name stays, so walk-in sales and old sales keep working.
            $table->unsignedBigInteger('customer_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};