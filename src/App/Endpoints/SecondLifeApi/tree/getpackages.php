<?php

$status = false;
$input = new inputFilter();
$tree_vender_id = $input->postFilter("tree_vender_id", "integer");
function value_or_zero($value)
{
    if ($value !== null) {
        return $value;
    }
    return 0;
}
if ($tree_vender_id > 0) {
    $treevender = new treevender();
    if ($treevender->load($tree_vender_id) == true) {
        $treevender_packages_set = new treevender_packages_set();
        $load_status = $treevender_packages_set->load_on_field("treevenderlink", $treevender->get_id());
        if ($load_status["status"] == true) {
            $package_set = new package_set();
            $load_status = $package_set->load_ids($treevender_packages_set->get_unique_array("packagelink"));
            if ($load_status["status"] == true) {
                $status = true;
                $reply["package_uid"] = array();
                $reply["package_autodj"] = array();
                $reply["package_autodjsize"] = array();
                $reply["package_listeners"] = array();
                $reply["package_bitrate"] = array();
                $reply["package_days"] = array();
                $reply["package_cost"] = array();
                $package_hashs = array();
                foreach ($treevender_packages_set->get_all_ids() as $treevender_package_id) {
                    $treevender_package = $treevender_packages_set->get_object_by_id($treevender_package_id);
                    $package = $package_set->get_object_by_id($treevender_package->get_packagelink());
                    $hash = sha1(implode(" ", array($package->get_autodj(),$package->get_autodj_size(),$package->get_listeners(),$package->get_bitrate(),$package->get_days(),$package->get_cost())));
                    if (in_array($hash, $package_hashs) == false) {
                        $package_hashs[] = $hash;
                        $reply["package_uid"][] = $package->get_package_uid();
                        $reply["package_autodj"][] = array(true => "Yes",false => "No")[$package->get_autodj()];
                        $reply["package_autodjsize"][] = value_or_zero($package->get_autodj_size());
                        $reply["package_listeners"][] = $package->get_listeners();
                        $reply["package_bitrate"][] = $package->get_bitrate();
                        $reply["package_days"][] = $package->get_days();
                        $reply["package_cost"][] = $package->get_cost();
                    }
                }
                echo $lang["tree.gp.info.1"];
            } else {
                echo $lang["tree.gp.error.4"];
            }
        } else {
            echo $lang["tree.gp.error.3"];
        }
    } else {
        echo $lang["tree.gp.error.2"];
    }
} else {
    echo $lang["tree.gp.error.1"];
}
