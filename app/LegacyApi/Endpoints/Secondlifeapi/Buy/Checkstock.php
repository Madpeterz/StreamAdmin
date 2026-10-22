<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Buy;

use App\LegacyApi\SlEndpoint;
use App\Models\Package;
use App\Models\Stream;

/** Whether a stream of this package is free to rent, and how the vendor shows it. */
class Checkstock extends SlEndpoint
{
    public function process(): void
    {
        $package = Package::where('package_uid', $this->input->string('packageuid'))->first();
        if ($package === null) {
            $this->set('message', 'Unable to find package');

            return;
        }
        $this->set('status', true);
        $this->set('message', 'ok');
        $this->set('package_instock', Stream::where('package_id', $package->id)->whereNull('rental_id')->where('need_work', false)->exists());
        $this->set('package_cost', $package->cost);
        $this->set('texture_package_small', $package->texture_instock_small);
        $this->set('texture_package_big', $package->texture_instock_selected);
        $this->set('texture_package_soldout', $package->texture_soldout);
    }
}
