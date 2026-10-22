<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff log in through Laravel auth with a bcrypt `password`. The 2.0.x hash columns are made
 * nullable here and dropped by 2026_10_23_000000_retire_legacy_compatibility.
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
