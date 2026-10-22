<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Buy;

use App\LegacyApi\SlEndpoint;
use App\Models\Package;
use App\Models\Stream;
use App\Models\TexturePack;

/** A rental vendor's setup: textures, price, stock and the reseller's rate. */
class Getconfig extends SlEndpoint
{
    public function process(): void
    {
        $packageUid = $this->input->string('packageuid');
        $texturePackId = $this->input->intMin('texturepack', 1);
        if (! $texturePackId) {
            $this->set('message', 'Invaild texturepack');

            return;
        }
        $pack = TexturePack::find($texturePackId);
        if ($pack === null) {
            $this->set('message', 'Unable to load texturepack');

            return;
        }
        $package = Package::where('package_uid', $packageUid)->first();
        if ($package === null) {
            $this->set('message', 'Unable to load package');

            return;
        }
        $this->set('status', true);
        $this->set('message', 'ok');
        $this->set('package_instock', Stream::where('package_id', $package->id)->whereNull('rental_id')->where('need_work', false)->exists());
        $this->set('Texture-Offline', $pack->offline);
        $this->set('Texture-WaitOwner', $pack->wait_owner);
        $this->set('Texture-GettingDetails', $pack->getting_details);
        $this->set('Texture-MakePayment', $pack->make_payment);
        $this->set('package_cost', $package->cost);
        $this->set('Texture-PackageSmall', $package->texture_instock_small);
        $this->set('Texture-PackageBig', $package->texture_instock_selected);
        $this->set('Texture-PackageSoldout', $package->texture_soldout);
        $this->set('reseller_rate', $this->ownerOverride ? 100 : $this->reseller->rate);
        $this->set('reseller_mode', $this->ownerOverride ? 'System owner mode' : 'Reseller mode');
    }
}
