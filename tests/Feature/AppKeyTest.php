<?php

namespace Tests\Feature;

use App\Support\AppKey;
use Illuminate\Encryption\Encrypter;
use Tests\TestCase;

class AppKeyTest extends TestCase
{
    public function test_valid_key_is_left_alone(): void
    {
        $key = 'base64:'.base64_encode(Encrypter::generateKey('AES-256-CBC'));

        $this->assertSame($key, AppKey::normalize($key));
    }

    public function test_valid_raw_32_character_key_is_left_alone(): void
    {
        $key = str_repeat('k', 32);

        $this->assertSame($key, AppKey::normalize($key));
    }

    public function test_quotes_and_whitespace_around_a_valid_key_are_removed(): void
    {
        $key = 'base64:'.base64_encode(Encrypter::generateKey('AES-256-CBC'));

        $this->assertSame($key, AppKey::normalize("  \"$key\"\n"));
    }

    public function test_empty_key_stays_empty(): void
    {
        $this->assertNull(AppKey::normalize(null));
        $this->assertSame('', AppKey::normalize(''));
    }

    public function test_invalid_key_becomes_a_usable_key(): void
    {
        foreach (['short', 'base64:not-valid-base64!', 'base64:'.base64_encode('too short'), str_repeat('x', 50)] as $seed) {
            $key = AppKey::normalize($seed);

            $this->assertStringStartsWith('base64:', $key);
            $this->assertTrue(Encrypter::supported(base64_decode(substr($key, 7), true), 'AES-256-CBC'));
        }
    }

    public function test_same_seed_gives_the_same_key_and_other_seeds_differ(): void
    {
        $this->assertSame(AppKey::normalize('my old key'), AppKey::normalize('my old key'));
        $this->assertSame(AppKey::normalize('my old key'), AppKey::normalize(' "my old key"'));
        $this->assertNotSame(AppKey::normalize('my old key'), AppKey::normalize('my other key'));
    }

    public function test_app_boots_with_a_malformed_app_key(): void
    {
        $key = AppKey::normalize('not a real key');
        config(['app.key' => $key]);

        $this->assertSame('hello', decrypt(encrypt('hello')));
    }
}
