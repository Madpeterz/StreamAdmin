<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Texturepack;

use App\LegacyApi\SlEndpoint;
use App\Models\TexturePack;

/** All textures of a texture pack, plus the calling reseller's rate. */
class Getpack extends SlEndpoint
{
    public function process(): void
    {
        $id = (int) $this->input->int('texturepack');
        if ($id < 1) {
            $this->failed('Invaild texturepack id (or non sent)');

            return;
        }
        $pack = TexturePack::find($id);
        if ($pack === null) {
            $this->failed('Unable to load texture pack');

            return;
        }
        $this->set('Texture-Offline', $pack->offline);
        $this->set('Texture-WaitOwner', $pack->wait_owner);
        $this->set('Texture-GettingDetails', $pack->getting_details);
        $this->set('Texture-MakePayment', $pack->make_payment);
        $this->set('Texture-RenewHere', $pack->renew_here);
        $this->set('Texture-InUse', $pack->in_use);
        $this->set('Texture-RequestDetails', $pack->request_details);
        $this->set('Texture-StockLevels', $pack->stock_levels);
        $this->set('Texture-ProxyRenew', $pack->proxy_renew);
        $this->set('reseller_rate', $this->ownerOverride ? 100 : $this->reseller->rate);
        $this->set('reseller_mode', $this->ownerOverride ? 'Owner mode' : 'Reseller mode');
        $this->ok('ok');
    }
}
