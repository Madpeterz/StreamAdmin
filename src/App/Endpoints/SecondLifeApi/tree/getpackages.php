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
    if ($treevender->loadID($tree_vender_id) == true) {
        $treevender_packages_set = new treevender_packages_set();
        $load_status = $treevender_packages_set->load_on_field("treevenderlink", $treevender->getId());
        if ($load_status["status"] == true) {
            $package_set = new package_set();
            $load_status = $package_set->loadIds($treevender_packages_set->get_unique_array("packagelink"));
            if ($load_status["status"] == true) {
                $status = true;
                $reply["package_uid"] = [];
                $reply["package_autodj"] = [];
                $reply["package_autodjsize"] = [];
                $reply["package_listeners"] = [];
                $reply["package_bitrate"] = [];
                $reply["package_days"] = [];
                $reply["package_cost"] = [];
                $package_hashs = [];
                foreach ($treevender_packages_set->getAllIds() as $treevender_package_id) {
                    $treevender_package = $treevender_packages_set->getObjectByID($treevender_package_id);
                    $package = $package_set->getObjectByID($treevender_package->get_packagelink());
                    $hash = sha1(implode(" ", [$package->getAutodj(),$package->getAutodj_size(),$package->getListeners(),$package->getBitrate(),$package->getDays(),$package->getCost()]));
                    if (in_array($hash, $package_hashs) == false) {
                        $package_hashs[] = $hash;
                        $reply["package_uid"][] = $package->getPackage_uid();
                        $reply["package_autodj"][] = [true => "Yes",false => "No"][$package->getAutodj()];
                        $reply["package_autodjsize"][] = value_or_zero($package->getAutodj_size());
                        $reply["package_listeners"][] = $package->getListeners();
                        $reply["package_bitrate"][] = $package->getBitrate();
                        $reply["package_days"][] = $package->getDays();
                        $reply["package_cost"][] = $package->getCost();
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
