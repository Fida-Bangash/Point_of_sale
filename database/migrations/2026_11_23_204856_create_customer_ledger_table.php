<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_ledger', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // A customer with history is never deleted (deactivate instead), so restrict.
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();

            $table->string('type', 30)->index();

            // Signed amount: positive = customer owes more (udhaar),
            // negative = customer paid / was credited (wasooli).
            $table->decimal('amount', 12, 2);

            // Points at the entry this one cancels. UNIQUE = an entry can be reversed only once.
            $table->unsignedBigInteger('reversal_of_id')->nullable()->unique();

            // Portable reference to the source document (sale, return, ...). A uuid survives restore.
            $table->string('ref_type', 30)->nullable();
            $table->uuid('ref_uuid')->nullable();

            $table->string('note', 500)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('entry_date');
            $table->timestamps();

            $table->index(['customer_id', 'entry_date']);
            $table->index(['ref_type', 'ref_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_ledger');
    }
};