<?php

namespace Tests\Feature;

use App\Models\SlConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_install_has_the_renamed_schema(): void
    {
        $this->assertTrue(Schema::hasTable('rentals'));
        $this->assertFalse(Schema::hasTable('rental'));
        $this->assertTrue(Schema::hasColumns('rentals', ['avatar_id', 'stream_id', 'expire_unixtime', 'rental_uid']));
        $this->assertTrue(Schema::hasColumns('staff', ['password', 'remember_token', 'owner_level']));
        $this->assertSame('3.0.0', SlConfig::find(1)->db_version);
    }

    public function test_foreign_keys_follow_the_renames(): void
    {
        $keys = DB::select(
            'SELECT table_name AS t, column_name AS c, referenced_table_name AS r FROM information_schema.key_column_usage
             WHERE table_schema = DATABASE() AND referenced_table_name IS NOT NULL'
        );

        $this->assertGreaterThanOrEqual(36, count($keys));
        $this->assertContains('rentals.avatar_id->avatars', array_map(fn ($key) => $key->t.'.'.$key->c.'->'.$key->r, $keys));
    }
}
