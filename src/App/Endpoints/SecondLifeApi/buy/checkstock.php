<?php

$input = new inputFilter();
$packageuid = $input->postFilter("packageuid");
$status = false;
$package = new package();
if ($package->loadByField("package_uid", $packageuid) == true) {
    $apirequests_set = new api_requests_set();
    $apirequests_set->loadAll();
    $used_stream_ids = $apirequests_set->getUniqueArray("streamlink");
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
        $status = true;
        $reply["package_instock"] = 0;
        if ($count_data["count"] > 0) {
            $reply["package_instock"] = 1;
        }
        $reply["package_cost"] = $package->getCost();
        $reply["texture_package_small"] = $package->getTexture_uuid_instock_small();
        $reply["texture_package_big"] = $package->getTexture_uuid_instock_selected();
        $reply["texture_package_soldout"] = $package->getTexture_uuid_soldout();
    } else {
        echo $lang["buy.cs.error.2"];
    }
} else {
    echo $lang["buy.cs.error.1"];
}
