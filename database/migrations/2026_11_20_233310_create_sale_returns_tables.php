<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per return "document" (a bill can have several partial returns).
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('return_no', 40)->unique();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('refund_total', 12, 2)->default(0);
            $table->string('refund_method', 30)->default('cash');
            $table->string('reason', 255)->nullable();
            $table->string('note', 500)->nullable();
            $table->dateTime('returned_at')->index();
            $table->timestamps();
        });

        // The lines of a return. Prices are snapshots: later price changes never touch history.
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
            // Nullable: if the sale line or product is deleted later, the return history stays
            $table->foreignId('sale_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('barcode', 100)->nullable();
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('purchase_price', 12, 2)->default(0);
            // Money handed back for this line (already net of the bill discount share)
            $table->decimal('refund_amount', 12, 2);
            // false = damaged / not resellable, so stock was NOT increased
            $table->boolean('restocked')->default(true);
            $table->timestamps();

            $table->index('sale_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
    }
};