<?php

namespace Tests\Feature;

use App\Models\Avatar;
use App\Models\Notice;
use App\Models\Region;
use App\Models\SlObject;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

/**
 * The upgrade converts what the 2.0.x compatibility layers handled (migration
 * 2026_10_23_000000_retire_legacy_compatibility). Its two conversions are run here on legacy
 * looking rows; dropping the staff hash columns is checked on the schema.
 */
class RetireLegacyCompatibilityTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    private function runMigration(string $method, string $table, string $column): void
    {
        $migration = require database_path('migrations/2026_10_23_000000_retire_legacy_compatibility.php');
        $reflection = new ReflectionMethod($migration, $method);
        $reflection->invoke($migration, $table, $column);
    }

    public function test_names_stored_with_html_entities_become_plain_text(): void
    {
        $avatar = Avatar::create(['avatar_uuid' => '0b1ec700-0000-4000-8000-000000000001', 'avatar_name' => 'Pat O&#039;Brien', 'avatar_uid' => 'ent00001', 'credits' => 0]);
        $plain = $this->avatar('Plain Name');
        $region = Region::create(['name' => 'Nova&#039;s Isle']);
        $object = SlObject::create([
            'avatar_id' => $avatar->id, 'region_id' => $region->id, 'object_uuid' => '0b1ec700-0000-4000-8000-000000000002', 'object_name' => 'Caf&eacute; &amp; Bar',
            'object_mode' => 'vendor', 'object_xyz' => '1,1,1', 'last_seen' => time(),
        ]);

        $this->runMigration('decodeEntities', 'avatars', 'avatar_name');
        $this->runMigration('decodeEntities', 'regions', 'name');
        $this->runMigration('decodeEntities', 'sl_objects', 'object_name');

        $this->assertSame("Pat O'Brien", $avatar->refresh()->avatar_name);
        $this->assertSame('Plain Name', $plain->refresh()->avatar_name);
        $this->assertSame("Nova's Isle", $region->refresh()->name);
        $this->assertSame('Café & Bar', $object->refresh()->object_name);
    }

    public function test_1x_swap_tags_are_renamed_in_templates_and_notices(): void
    {
        Template::findOrFail(1)->update(['detail' => 'Hi [[FirstName]] [[LastName]], port [[PortNum]] of [[Package]] ([[Kbps]]kbps) [[AVATAR_FIRSTNAME]]']);
        Notice::findOrFail(1)->update(['im_message' => '[[TimeLeft]] left, expires [[ExpiresAt]]']);

        $this->runMigration('renameTags', 'templates', 'detail');
        $this->runMigration('renameTags', 'notices', 'im_message');

        $this->assertSame('Hi [[AVATAR_FIRSTNAME]] [[AVATAR_LASTNAME]], port [[STREAM_PORT]] of [[PACKAGE_NAME]] ([[PACKAGE_BITRATE]]kbps) [[AVATAR_FIRSTNAME]]', Template::findOrFail(1)->detail);
        $this->assertSame('[[RENTAL_TIMELEFT]] left, expires [[RENTAL_EXPIRES_DATETIME]]', Notice::findOrFail(1)->im_message);
    }

    public function test_the_staff_hash_columns_are_gone(): void
    {
        foreach (['phash', 'psalt', 'lhash'] as $column) {
            $this->assertFalse(Schema::hasColumn('staff', $column), $column);
        }
        $this->assertTrue(Schema::hasColumn('staff', 'password'));
        $this->assertTrue(Schema::hasColumn('sl_configs', 'owner_link_code'));
    }
}
