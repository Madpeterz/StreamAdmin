<?php

namespace App\Endpoints\SecondLifeApi\Buy;

use App\Models\ApirequestsSet;
use App\Models\Package;
use App\Models\Stream;
use App\Models\Textureconfig;
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
            $this->output->setSwapTagString("message", "Invaild texturepack");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($texturepack) == false) {
            $this->output->setSwapTagString("message", "Unable to load texturepack");
            return;
        }
        $package = new Package();
        if ($package->loadByField("package_uid", $packageuid) == false) {
            $this->output->setSwapTagString("message", "Unable to load package");
            return;
        }
        // $reseller, $object_owner_avatar, $owner_override, $region, $object
        $apirequests_set = new ApirequestsSet();
        $apirequests_set->loadAll();
        $used_stream_ids = $apirequests_set->getUniqueArray("streamlink");
        // package_instock,
        $stream = new Stream();
        $whereconfig = [
            "fields" => ["rentallink","packagelink","needwork"],
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
            $this->output->setSwapTagString("message", "Unable to check if package is in stock");
            return;
        }

        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("package_instock", "0");
        if ($count_data["count"] > 0) {
            $this->output->setSwapTagString("package_instock", "1");
        }
        $this->output->setSwapTagString("texture_offline", $textureconfig->getOffline());
        $this->output->setSwapTagString("texture_waitingforowner", $textureconfig->getWait_owner());
        $this->output->setSwapTagString("texture_fetchingdetails", $textureconfig->getGetting_details());
        $this->output->setSwapTagString("texture_request_payment", $textureconfig->getMake_payment());
        $this->output->setSwapTagString("package_cost", $package->getCost());
        $this->output->setSwapTagString("texture_package_small", $package->getTexture_uuid_instock_small());
        $this->output->setSwapTagString("texture_package_big", $package->getTexture_uuid_instock_selected());
        $this->output->setSwapTagString("texture_package_soldout", $package->getTexture_uuid_soldout());
        $this->output->setSwapTagString("reseller_rate", 100);
        $this->output->setSwapTagString("reseller_mode", "System owner mode");
        if ($this->owner_override == false) {
            $this->output->setSwapTagString("reseller_rate", $this->reseller->getRate());
            $this->output->setSwapTagString("reseller_mode", "Reseller mode");
        }
    }
}
