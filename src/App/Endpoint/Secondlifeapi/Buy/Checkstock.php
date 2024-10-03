<?php

namespace App\Endpoint\Secondlifeapi\Buy;

use App\Models\Package;
use App\Models\Sets\StreamSet;
use App\Template\SecondlifeAjax;

class Checkstock extends SecondlifeAjax
{
    public function process(): void
    {
        $packageuid = $this->input->post("packageuid")->asString();
        $package = new Package();
        if ($package->loadByPackageUid($packageuid)->status == false) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        $whereconfig = [
            "fields" => ["rentalLink", "packageLink", "needWork"],
            "matches" => ["IS", "=", "="],
            "values" => [null, $package->getId(), 0],
            "types" => ["i", "i", "i"],
        ];
        $streamSet = new StreamSet();
        $count_data = $streamSet->countInDB($whereconfig);
        if ($count_data->status == false) {
            $this->setSwapTag("message", "Unable to check stock level");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("package_instock", false);
        if ($count_data->items > 0) {
            $this->setSwapTag("package_instock", true);
        }
        $this->setSwapTag("package_cost", $package->getCost());
        $this->setSwapTag("texture_package_small", $package->getTextureInstockSmall());
        $this->setSwapTag("texture_package_big", $package->getTextureInstockSelected());
        $this->setSwapTag("texture_package_soldout", $package->getTextureSoldout());
    }
}
