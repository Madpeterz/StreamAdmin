<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * A separate link code for the system owner's own objects (mail, details and notice servers, bot
 * queues, owner vendors). A request that says it comes from the owner avatar must be signed with
 * it, so resellers, who all share the vendor link code, can no longer pose as the owner.
 *
 * The 2.0.x link code columns were 10 and 12 characters wide; they are widened so the 16 character
 * keys issued from 3.0 on fit (existing values are kept).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sl_configs', function (Blueprint $table) {
            $table->string('sl_link_code', 64)->change();
            $table->string('public_link_code', 64)->nullable()->change();
            $table->string('hud_link_code', 64)->nullable()->change();
            $table->string('owner_link_code', 64)->nullable();
        });
        DB::table('sl_configs')->update(['owner_link_code' => Str::random(16)]);
    }

    public function down(): void
    {
        Schema::table('sl_configs', function (Blueprint $table) {
            $table->dropColumn('owner_link_code');
        });
    }
};
