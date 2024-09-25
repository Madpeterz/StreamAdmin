<?php

namespace App\Endpoint\Secondlifeapi\Buy;

use App\Models\Package;
use App\Models\Sets\StreamSet;
use App\Models\Textureconfig;
use App\Template\SecondlifeAjax;

class Getconfig extends SecondlifeAjax
{
    public function process(): void
    {
        $packageuid = $this->input->post("packageuid")->asString();
        $texturepack = $this->input->post("texturepack")->checkGrtThanEq(1)->asInt();
        if ($texturepack == null) {
            $this->setSwapTag("message", "Invaild texturepack");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($texturepack) == false) {
            $this->setSwapTag("message", "Unable to load texturepack");
            return;
        }
        $package = new Package();
        if ($package->loadByPackageUid($packageuid) == false) {
            $this->setSwapTag("message", "Unable to load package");
            return;
        }
        $whereconfig = [
            "fields" => ["rentalLink","packageLink","needWork"],
            "matches" => ["IS","=","="],
            "values" => [null,$package->getId(),0],
            "types" => ["i","i","i"],
        ];
        $streamSet = new StreamSet();
        $count_data = $streamSet->countInDB($whereconfig);
        if ($count_data->status == false) {
            $this->setSwapTag("message", "Unable to check if package is in stock");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("package_instock", false);
        if ($count_data->items > 0) {
            $this->setSwapTag("package_instock", true);
        }
        $this->setSwapTag("Texture-Offline", $textureconfig->getOffline());
        $this->setSwapTag("Texture-WaitOwner", $textureconfig->getWaitOwner());
        $this->setSwapTag("Texture-GettingDetails", $textureconfig->getGettingDetails());
        $this->setSwapTag("Texture-MakePayment", $textureconfig->getMakePayment());
        $this->setSwapTag("package_cost", $package->getCost());
        $this->setSwapTag("Texture-PackageSmall", $package->getTextureInstockSmall());
        $this->setSwapTag("Texture-PackageBig", $package->getTextureInstockSelected());
        $this->setSwapTag("Texture-PackageSoldout", $package->getTextureSoldout());
        $this->setSwapTag("reseller_rate", 100);
        $this->setSwapTag("reseller_mode", "System owner mode");
        if ($this->owner_override == false) {
            $this->setSwapTag("reseller_rate", $this->reseller->getRate());
            $this->setSwapTag("reseller_mode", "Reseller mode");
        }
    }
}
