<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 3.0 drops the 2.0.x compatibility layers, so the data they handled is converted once:
 *
 * - names the in-world API stored with htmlentities() (avatars, regions, objects, notecards) are
 *   stored as plain text; pages escape on output;
 * - the 1.x swap tags ([[FirstName]], [[PortNum]]...) in templates and notices become the current
 *   tags, since they are no longer understood;
 * - the 2.0.x staff password hash columns go. Staff who never signed in on 3.0.x have no password
 *   and need `php artisan streamadmin:staff-password <username>` (or the in-world reset link).
 */
return new class extends Migration
{
    /** 1.x tag => current tag */
    private const ALIASES = [
        'FirstName' => 'AVATAR_FIRSTNAME',
        'LastName' => 'AVATAR_LASTNAME',
        'ExpiresAt' => 'RENTAL_EXPIRES_DATETIME',
        'TimeLeft' => 'RENTAL_TIMELEFT',
        'PortNum' => 'STREAM_PORT',
        'AdminUsername' => 'STREAM_ADMINUSERNAME',
        'AdminPassword' => 'STREAM_ADMINPASSWORD',
        'DjPassword' => 'STREAM_DJPASSWORD',
        'MountPoint' => 'STREAM_MOUNTPOINT',
        'Package' => 'PACKAGE_NAME',
        'Users' => 'PACKAGE_LISTENERS',
        'Kbps' => 'PACKAGE_BITRATE',
        'AutoDJ' => 'PACKAGE_AUTODJ',
        'Disk' => 'PACKAGE_AUTODJ_SIZE',
        'PackUID' => 'PACKAGE_UID',
        'RentUID' => 'RENTAL_UID',
    ];

    public function up(): void
    {
        foreach ([['avatars', 'avatar_name'], ['regions', 'name'], ['sl_objects', 'object_name'], ['notice_notecards', 'name']] as [$table, $column]) {
            $this->decodeEntities($table, $column);
        }
        foreach ([['templates', 'detail'], ['templates', 'notecard_detail'], ['notices', 'im_message'], ['notices', 'notecard_detail']] as [$table, $column]) {
            $this->renameTags($table, $column);
        }
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['phash', 'psalt', 'lhash']);
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('phash', 64)->nullable();
            $table->string('psalt', 64)->nullable();
            $table->string('lhash', 64)->nullable();
        });
    }

    private function decodeEntities(string $table, string $column): void
    {
        DB::table($table)->where($column, 'like', '%&%;%')->orderBy('id')->each(function (object $row) use ($table, $column) {
            $decoded = html_entity_decode((string) $row->{$column}, ENT_QUOTES | ENT_HTML401);
            if ($decoded !== $row->{$column}) {
                DB::table($table)->where('id', $row->id)->update([$column => $decoded]);
            }
        });
    }

    private function renameTags(string $table, string $column): void
    {
        $map = [];
        foreach (self::ALIASES as $alias => $tag) {
            $map['[['.$alias.']]'] = '[['.$tag.']]';
        }
        DB::table($table)->where($column, 'like', '%[[%')->orderBy('id')->each(function (object $row) use ($table, $column, $map) {
            $text = strtr((string) $row->{$column}, $map);
            if ($text !== $row->{$column}) {
                DB::table($table)->where('id', $row->id)->update([$column => $text]);
            }
        });
    }
};
