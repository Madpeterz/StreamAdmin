<?php

namespace App\Api\Http\Controllers;

use App\Services\VendorCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Stock, prices and textures for vendors, stock boards and tree vendors. */
class CatalogController extends ApiController
{
    public function __construct(private readonly VendorCatalog $catalog) {}

    public function packageStatus(Request $request): JsonResponse
    {
        $data = $this->data($request, ['packageUid' => ['required', 'string', 'max:16']]);

        return $this->ok('ok', $this->catalog->packageStatus($data['packageUid']));
    }

    public function vendorConfig(Request $request): JsonResponse
    {
        $data = $this->data($request, ['packageUid' => ['required', 'string', 'max:16'], 'texturePackId' => ['required', 'integer', 'min:1']]);

        return $this->ok('ok', $this->catalog->vendorConfig($this->caller($request), $data['packageUid'], (int) $data['texturePackId']));
    }

    public function texturePack(Request $request): JsonResponse
    {
        $data = $this->data($request, ['texturePackId' => ['required', 'integer', 'min:1']]);

        return $this->ok('ok', $this->catalog->texturePack($this->caller($request), (int) $data['texturePackId']));
    }

    public function stockLevels(): JsonResponse
    {
        return $this->ok('ok', ['servers' => $this->catalog->stockLevels()]);
    }

    public function treePackages(Request $request): JsonResponse
    {
        $data = $this->data($request, ['treeVendorId' => ['required', 'integer', 'min:1']]);

        return $this->ok('ok', $this->catalog->treePackages((int) $data['treeVendorId']));
    }
}
