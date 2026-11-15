<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shop_settings', function (Blueprint $t) {
            $t->uuid('uuid')->nullable()->after('id');
            $t->string('code', 20)->default('main')->after('uuid');
        });

        // Default row ko uuid do. Queue mein NAHI daalna: khali default row
        // server ke asli data ko overwrite na kare.
        DB::table('shop_settings')->whereNull('uuid')->update(['uuid' => (string) Str::uuid()]);

        Schema::table('shop_settings', function (Blueprint $t) {
            $t->unique('uuid');
            $t->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::table('shop_settings', function (Blueprint $t) {
            $t->dropUnique(['uuid']);
            $t->dropUnique(['code']);
            $t->dropColumn(['uuid', 'code']);
        });
    }
};
