<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('held_bills', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('customer_name')->nullable();
            $t->decimal('discount', 12, 2)->default(0);
            $t->decimal('total', 12, 2)->default(0);
            // Snapshot of the cart: product id/uuid, barcode, sku, name, price, quantity
            $t->json('items');
            $t->dateTime('held_at');
            $t->dateTime('expires_at')->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('held_bills');
    }
};