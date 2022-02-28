<?php

namespace App\Endpoint\SecondLifeApi\Buy;

use App\Models\Sets\ApirequestsSet;
use App\Models\Package;
use App\Models\Stream;
use App\Template\SecondlifeAjax;

class Checkstock extends SecondlifeAjax
{
    public function process(): void
    {

        $packageuid = $this->post("packageuid");
        $package = new Package();
        if ($package->loadByField("packageUid", $packageuid) == false) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        $apirequests_set = new ApirequestsSet();
        $apirequests_set->loadAll();
        $used_stream_ids = $apirequests_set->getUniqueArray("streamLink");
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
        $count_data = $this->siteConfig->getSQL()->basicCountV2($stream->getTable(), $whereconfig);
        if ($count_data["status"] == false) {
            $this->setSwapTag("message", "Unable to check stock level");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("package_instock", false);
        if ($count_data["count"] > 0) {
            $this->setSwapTag("package_instock", true);
        }
        $this->setSwapTag("package_cost", $package->getCost());
        $this->setSwapTag("texture_package_small", $package->getTextureInstockSmall());
        $this->setSwapTag("texture_package_big", $package->getTextureInstockSelected());
        $this->setSwapTag("texture_package_soldout", $package->getTextureSoldout());
    }
}
