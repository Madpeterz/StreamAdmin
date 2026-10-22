<?php

namespace App\Api\Http\Middleware;

use App\Api\ApiClass;
use App\Api\ApiException;
use App\Api\Caller;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Services\AvatarDirectory;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Works out who is calling from the key class and the "object" and "owner" parts of the body:
 *
 *   "object": {"key": uuid, "name", "type", "region", "position"}   the calling object
 *   "owner":  {"key": uuid, "name"}                                 its owner (the HUD's wearer)
 *
 * The owner class is always the system owner, so it sends no "owner". Vendor keys may not name the
 * system owner. Free text is sent through llEscapeURL and decoded here. The call is recorded as
 * the object being seen (vendors and owner objects), and a new reseller is approved or not as the
 * System settings say.
 */
class IdentifyCaller
{
    public function __construct(private readonly AvatarDirectory $avatars) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var ApiClass $class */
        $class = $request->attributes->get('api.class');
        /** @var array<string, mixed> $body */
        $body = $request->attributes->get('api.body');
        $tracked = $class !== ApiClass::Hud;

        $grid = $request->header('X-SecondLife-Shard');
        if ($grid !== 'Production') {
            throw ApiException::rejected('Wrong grid connected: '.($grid ?? 'none'));
        }
        $identity = $this->validated($body, $class, $tracked);

        $config = SlConfig::with('ownerAvatar')->findOrFail(1);
        if ($class === ApiClass::Owner) {
            $avatar = $config->ownerAvatar ?? throw ApiException::rejected('The system owner avatar is not set up');
        } else {
            $avatar = $this->avatars->findOrCreate($identity['owner']['key'], $this->text($identity['owner']['name']));
            if ($class === ApiClass::Vendor && $avatar->id === $config->owner_avatar_id) {
                throw ApiException::forbidden('The system owner must sign with the owner key');
            }
        }
        $region = Region::firstOrCreate(['name' => $this->text($identity['object']['region'])]);

        $reseller = null;
        $object = null;
        if ($tracked) {
            $reseller = $class === ApiClass::Owner
                ? Reseller::firstOrCreate(['avatar_id' => $avatar->id], ['allowed' => true, 'rate' => 0])
                : Reseller::firstOrCreate(['avatar_id' => $avatar->id], ['allowed' => (bool) $config->new_resellers, 'rate' => (int) $config->new_resellers_rate]);
            if (! $reseller->allowed) {
                throw ApiException::forbidden('This reseller is not approved, please contact the owner');
            }
            $object = $this->track($identity['object'], $avatar->id, $region->id);
        }
        $request->attributes->set('api.caller', new Caller($class, $avatar, $region, $reseller, $object));

        return $next($request);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array{object: array<string, string>, owner: array<string, string>}
     */
    private function validated(array $body, ApiClass $class, bool $tracked): array
    {
        $rules = [
            'object' => ['required', 'array'],
            'object.region' => ['required', 'string', 'min:3', 'max:255'],
            'owner' => [$class === ApiClass::Owner ? 'nullable' : 'required', 'array'],
            'owner.key' => ['required_with:owner', 'uuid'],
            'owner.name' => ['required_with:owner', 'string', 'min:1', 'max:255'],
        ];
        if ($tracked) {
            $rules += [
                'object.key' => ['required', 'uuid'],
                'object.name' => ['required', 'string', 'min:1', 'max:255'],
                'object.type' => ['required', 'string', 'min:1', 'max:64'],
                'object.position' => ['required', 'string', 'min:1', 'max:64'],
            ];
        }
        try {
            $validator = Validator::make($body, $rules);

            return $validator->validate() + ['owner' => []];
        } catch (ValidationException $e) {
            throw ApiException::validation($e->validator->errors()->first(), $e->errors());
        }
    }

    private function text(string $value): string
    {
        return rawurldecode($value);
    }

    /** @param array<string, string> $object */
    private function track(array $object, int $avatarId, int $regionId): SlObject
    {
        $existing = SlObject::where('object_uuid', $object['key'])->first();
        if ($existing === null) {
            return SlObject::create([
                'avatar_id' => $avatarId,
                'region_id' => $regionId,
                'object_uuid' => $object['key'],
                'object_name' => $this->text($object['name']),
                'object_mode' => $this->text($object['type']),
                'object_xyz' => $this->text($object['position']),
                'last_seen' => time(),
            ]);
        }
        $existing->fill(['object_name' => $this->text($object['name']), 'region_id' => $regionId, 'last_seen' => time()])->save();

        return $existing;
    }
}
