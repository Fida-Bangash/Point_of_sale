<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_queue', function (Blueprint $t) {
            $t->id();
            $t->string('table_name');
            $t->uuid('uuid');
            $t->string('action');
            $t->unsignedInteger('tries')->default(0);
            $t->string('last_error')->nullable();
            $t->timestamps();
            $t->unique(['table_name', 'uuid']);
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
        });

        foreach (['units', 'categories', 'brands', 'products', 'users'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->uuid('uuid')->nullable()->after('id'));

            foreach (DB::table($table)->pluck('id') as $id) {
                $uuid = (string) Str::uuid();
                DB::table($table)->where('id', $id)->update(['uuid' => $uuid]);
                DB::table('sync_queue')->insert([
                    'table_name' => $table,
                    'uuid'       => $uuid,
                    'action'     => 'upsert',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table($table, fn (Blueprint $t) => $t->unique('uuid'));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_queue');
        Schema::dropIfExists('settings');
    }
};