<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff log in through Laravel auth. `password` is filled with a bcrypt hash the first time a
 * staff member logs in with their 2.0.x password (see App\Auth\LegacyStaffUserProvider), and the
 * old hash columns are then cleared to NULL (they keep their 2.0.x unique indexes, which allow NULLs).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('password')->nullable()->after('username');
            $table->rememberToken()->after('password');
            $table->string('phash', 64)->nullable()->change();
            $table->string('psalt', 64)->nullable()->change();
            $table->string('lhash', 64)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token']);
        });
    }
};
