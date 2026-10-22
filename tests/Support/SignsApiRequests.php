<?php

namespace Tests\Support;

use App\Api\ApiClass;
use App\Api\Signature;
use App\Models\Avatar;
use App\Models\Reseller;
use App\Models\SlConfig;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

/**
 * Sends signed /api/v3 calls the way an in-world script does: JSON body, X-SA-* headers.
 */
trait SignsApiRequests
{
    /** Give every key class a usable (16+ characters) secret; the installer's are too short. */
    protected function useApiKeys(): SlConfig
    {
        $config = SlConfig::findOrFail(1);
        if (strlen($config->sl_link_code) < Signature::MIN_SECRET_LENGTH || $config->sl_link_code === 'install') {
            $config->update([
                'sl_link_code' => 'vendor-secret-0123456789',
                'owner_link_code' => 'owner-secret-0123456789',
                'hud_link_code' => 'hud-secret-0123456789',
                'http_inbound_secret' => 'bot-secret-0123456789',
            ]);
        }

        return $config->refresh();
    }

    /**
     * @param  array<string, mixed>  $data  the endpoint's "data"
     * @param  array<string, mixed>  $body  extra top level parts ("object", "owner") replacing the defaults
     * @param  array<string, string>  $headers  replace or add headers (a null value removes one)
     */
    protected function api(string $path, array $data = [], ApiClass $class = ApiClass::Vendor, array $body = [], array $headers = [], ?string $secret = null, ?string $raw = null): TestResponse
    {
        $config = $this->useApiKeys();
        $body += ['object' => $this->callingObject()];
        if ($class !== ApiClass::Owner && $class !== ApiClass::Bot) {
            $body += ['owner' => $this->reseller()];
        }
        $body['data'] = $data === [] ? new \stdClass : $data;

        $json = $raw ?? json_encode($body, JSON_UNESCAPED_SLASHES);
        $path = '/'.ltrim($path, '/');
        $timestamp = (string) time();
        $nonce = Str::random(24);
        $headers = $headers + [
            'X-SA-Class' => $class->value,
            'X-SA-Timestamp' => $timestamp,
            'X-SA-Nonce' => $nonce,
            'X-SA-Signature' => Signature::sign($secret ?? $class->secret($config), $timestamp, $nonce, $path, $json),
            'X-SecondLife-Shard' => 'Production',
            'Content-Type' => 'application/json',
        ];

        return $this->call('POST', $path, [], [], [], $this->transformHeadersToServerVars(array_filter($headers, fn ($value) => $value !== null)), $json);
    }

    /** @return array<string, string> the "object" part: a vendor in Test Region (names are URL escaped by scripts) */
    protected function callingObject(string $name = 'Test Vendor', string $type = 'vendor'): array
    {
        return ['key' => (string) Str::uuid(), 'name' => rawurlencode($name), 'type' => $type, 'region' => rawurlencode('Test Region'), 'position' => '128,128,25'];
    }

    /**
     * An approved reseller, as the "owner" part of a vendor call.
     *
     * @return array{key: string, name: string}
     */
    protected function reseller(int $rate = 10, ?Avatar $avatar = null): array
    {
        $avatar ??= Avatar::firstOrCreate(['avatar_name' => 'Test Reseller'], [
            'avatar_uuid' => '6e5a6f0e-0000-4000-8000-000000000001', 'avatar_uid' => 'resel001', 'credits' => 0,
        ]);
        Reseller::firstOrCreate(['avatar_id' => $avatar->id], ['allowed' => true, 'rate' => $rate]);

        return ['key' => $avatar->avatar_uuid, 'name' => rawurlencode($avatar->avatar_name)];
    }

    /** @return array{key: string, name: string} */
    protected function identityOf(Avatar $avatar): array
    {
        return ['key' => $avatar->avatar_uuid, 'name' => rawurlencode($avatar->avatar_name)];
    }
}
