<?php
$input = new inputFilter();
$packageuid = $input->postFilter("packageuid");
$texturepack = $input->postFilter("texturepack","integer");
$status = false;
if($texturepack > 0)
{
    $textureconfig = new textureconfig();
    if($textureconfig->load($texturepack) == true)
    {
        $package = new package();
        if($package->load_by_field("package_uid",$packageuid) == true)
        {
            // $reseller, $object_owner_avatar, $owner_override, $region, $object

            // package_instock,
            $stream = new stream();
            $where_fields = array(array("rentallink"=>"IS"),array("packagelink"=>"="));
            $where_values = array(array(NULL => "i"),array($package->get_id() => "i"));
            $count_data = $sql->basic_count($stream->get_table(),$where_fields,$where_values);
            if($count_data["status"] == true)
            {
                // array("status"=>true, "count"=>$load_data["dataSet"][0]["sqlCount"]);
                $reply["package_instock"] = 0;
                $status = true;
                if($count_data["count"] > 0) $reply["package_instock"] = 1;
                $reply["texture_offline"] = $textureconfig->get_offline();
                $reply["texture_waitingforowner"] = $textureconfig->get_wait_owner();
                $reply["texture_fetchingdetails"] = $textureconfig->get_getting_details();
                $reply["texture_request_payment"] = $textureconfig->get_make_payment();

                $reply["package_cost"] = $package->get_cost();
                $reply["texture_package_small"] = $package->get_texture_uuid_instock_small();
                $reply["texture_package_big"] = $package->get_texture_uuid_instock_selected();
                $reply["texture_package_soldout"] = $package->get_texture_uuid_soldout();
                if($owner_override == false)
                {
                    $reply["reseller_rate"] = $reseller->get_rate();
                    $reply["reseller_mode"] = $lang["buy.gc.info.1"];
                }
                else
                {
                    $reply["reseller_rate"] = 100;
                    $reply["reseller_mode"] = $lang["buy.gc.info.2"];
                }
            }
            else
            {
                echo $lang["buy.gc.error.4"];
            }
        }
        else
        {
            echo $lang["buy.gc.error.3"];
        }
    }
    else
    {
        echo $lang["buy.gc.error.2"];
    }
}
else
{
    echo $lang["buy.gc.error.1"];
}
?>
