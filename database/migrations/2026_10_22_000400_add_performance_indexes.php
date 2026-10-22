<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the columns the busiest queries filter or sort on. The 2.0.x schema only indexed
 * the foreign keys, so these scanned and sorted the whole table:
 *
 *  - rentals.expire_unixtime: the notice server (every few seconds), "next to expire", client search
 *  - transactions (unixtime, amount): newest first lists, report date ranges and the by-year totals,
 *    which read both columns and so never touch the table itself
 *  - audit_logs.unixtime / store: the audit log is sorted newest first and polled; the area filter
 *    lists the distinct stores
 *  - avatars.avatar_name: avatar pickers and searches sort by name
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', fn (Blueprint $table) => $table->index('expire_unixtime', 'rentalExpire'));
        Schema::table('transactions', fn (Blueprint $table) => $table->index(['unixtime', 'amount'], 'transactionTime'));
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('unixtime', 'auditTime');
            $table->index('store', 'auditStore');
        });
        Schema::table('avatars', fn (Blueprint $table) => $table->index('avatar_name', 'avatarName'));
    }

    public function down(): void
    {
        Schema::table('rentals', fn (Blueprint $table) => $table->dropIndex('rentalExpire'));
        Schema::table('transactions', fn (Blueprint $table) => $table->dropIndex('transactionTime'));
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('auditTime');
            $table->dropIndex('auditStore');
        });
        Schema::table('avatars', fn (Blueprint $table) => $table->dropIndex('avatarName'));
    }
};
