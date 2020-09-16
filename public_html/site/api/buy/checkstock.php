<?php
$input = new inputFilter();
$packageuid = $input->postFilter("packageuid");
$status = false;
$package = new package();
if($package->load_by_field("package_uid",$packageuid) == true)
{
    $apirequests_set = new api_requests_set();
    $apirequests_set->loadAll();
    $used_stream_ids = $apirequests_set->get_unique_array("streamlink");
    $stream = new stream();
    $where_fields = array(array("rentallink"=>"IS"),array("packagelink"=>"="),array("needwork"=>"="),array("id" => "NOT IN"));
    $where_values = array(array(NULL => "i"),array($package->get_id() => "i"),array(0 => "i"),array($used_stream_ids => "i"));
    $count_data = $sql->basic_count($stream->get_table(),$where_fields,$where_values);
    if($count_data["status"] == true)
    {
        $status = true;
        $reply["package_instock"] = 0;
        if($count_data["count"] > 0) $reply["package_instock"] = 1;
        $reply["package_cost"] = $package->get_cost();
        $reply["texture_package_small"] = $package->get_texture_uuid_instock_small();
        $reply["texture_package_big"] = $package->get_texture_uuid_instock_selected();
        $reply["texture_package_soldout"] = $package->get_texture_uuid_soldout();
    }
    else
    {
        echo $lang["buy.cs.error.2"];
    }
}
else
{
    echo $lang["buy.cs.error.1"];
}
?>
