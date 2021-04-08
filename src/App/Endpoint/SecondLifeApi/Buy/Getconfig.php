<?php

namespace App\Endpoint\SecondLifeApi\Buy;

use App\R7\Set\ApirequestsSet;
use App\R7\Model\Package;
use App\R7\Model\Stream;
use App\R7\Model\Textureconfig;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Getconfig extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $packageuid = $input->postFilter("packageuid");
        $texturepack = $input->postFilter("texturepack", "integer");
        if ($texturepack <= 0) {
            $this->setSwapTag("message", "Invaild texturepack");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($texturepack) == false) {
            $this->setSwapTag("message", "Unable to load texturepack");
            return;
        }
        $package = new Package();
        if ($package->loadByField("packageUid", $packageuid) == false) {
            $this->setSwapTag("message", "Unable to load package");
            return;
        }
        // $reseller, $Object_OwnerAvatar, $owner_override, $region, $object
        $apirequests_set = new ApirequestsSet();
        $apirequests_set->loadAll();
        $used_stream_ids = $apirequests_set->getUniqueArray("streamLink");
        // package_instock,
        $stream = new Stream();
        $whereconfig = [
            "fields" => ["rentalLink","packageLink","needWork"],
            "matches" => ["IS","=","="],
            "values" => [null,$package->getId(),0],
            "types" => ["i","i","i"],
        ];
        if (count($used_stream_ids) > 0) {
            $whereconfig["fields"][] = "id";
            $whereconfig["matches"][] = "NOT IN";
            $whereconfig["values"][] = $used_stream_ids;
            $whereconfig["types"][] = "i";
        }
        $count_data = $this->sql->basicCountV2($stream->getTable(), $whereconfig);
        if ($count_data["status"] == false) {
            $this->setSwapTag("message", "Unable to check if package is in stock");
            return;
        }

        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("package_instock", false);
        if ($count_data["count"] > 0) {
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
