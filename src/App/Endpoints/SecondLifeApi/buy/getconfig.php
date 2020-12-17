<?php

$input = new inputFilter();
$packageuid = $input->postFilter("packageuid");
$texturepack = $input->postFilter("texturepack", "integer");
$status = false;
if ($texturepack > 0) {
    $textureconfig = new textureconfig();
    if ($textureconfig->loadID($texturepack) == true) {
        $package = new package();
        if ($package->loadByField("package_uid", $packageuid) == true) {
            // $reseller, $object_owner_avatar, $owner_override, $region, $object
            $apirequests_set = new api_requests_set();
            $apirequests_set->loadAll();
            $used_stream_ids = $apirequests_set->getUniqueArray("streamlink");
            // package_instock,
            $stream = new stream();
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
            $count_data = $sql->basic_count_v2($stream->getTable(), $whereconfig);
            if ($count_data["status"] == true) {
                // array("status"=>true, "count"=>$load_data["dataSet"][0]["sqlCount"]);
                $reply["package_instock"] = 0;
                $status = true;
                if ($count_data["count"] > 0) {
                    $reply["package_instock"] = 1;
                }
                $reply["texture_offline"] = $textureconfig->getOffline();
                $reply["texture_waitingforowner"] = $textureconfig->getWait_owner();
                $reply["texture_fetchingdetails"] = $textureconfig->getGetting_details();
                $reply["texture_request_payment"] = $textureconfig->getMake_payment();

                $reply["package_cost"] = $package->getCost();
                $reply["texture_package_small"] = $package->getTexture_uuid_instock_small();
                $reply["texture_package_big"] = $package->getTexture_uuid_instock_selected();
                $reply["texture_package_soldout"] = $package->getTexture_uuid_soldout();
                if ($owner_override == false) {
                    $reply["reseller_rate"] = $reseller->getRate();
                    $reply["reseller_mode"] = $lang["buy.gc.info.1"];
                } else {
                    $reply["reseller_rate"] = 100;
                    $reply["reseller_mode"] = $lang["buy.gc.info.2"];
                }
            } else {
                echo $lang["buy.gc.error.4"];
            }
        } else {
            echo $lang["buy.gc.error.3"];
        }
    } else {
        echo $lang["buy.gc.error.2"];
    }
} else {
    echo $lang["buy.gc.error.1"];
}
