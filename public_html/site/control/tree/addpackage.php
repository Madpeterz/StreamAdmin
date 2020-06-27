<?php
$input = new inputFilter();
$package_id = $input->postFilter("package","integer");

$status = false;
$treevender = new treevender();
$redirect = "tree";
if($treevender->load($page) == true)
{
    if($package_id > 0)
    {
        $package = new package();
        if($package->load($package_id) == true)
        {
            $treevender_package = new treevender_packages();
            $where_fields = array(
                "fields" => array("packagelink","treevenderlink"),
                "values" => array($package->get_id(),$treevender->get_id()),
                "types" => array("i","i"),
                "matches" => array("=","=")
            );
            if($treevender_package->load_with_config($where_fields) == false)
            {
                $treevender_package = new treevender_packages();
                $treevender_package->set_field("packagelink",$package->get_id());
                $treevender_package->set_field("treevenderlink",$treevender->get_id());
                $create_status = $treevender_package->create_entry();
                if($create_status["status"] == true)
                {
                    $redirect = "tree/manage/".$page."";
                    echo $lang["tree.ap.info.1"];
                    $status = true;
                }
                else
                {
                    echo $lang["tree.ap.error.5"];
                }
            }
            else
            {
                $redirect = "";
                echo $lang["tree.ap.error.4"];
            }
        }
        else
        {
            echo $lang["tree.ap.error.3"];
        }
    }
    else
    {
        echo $lang["tree.ap.error.2"];
    }
}
else
{
    echo $lang["tree.ap.error.1"];

}
?>
