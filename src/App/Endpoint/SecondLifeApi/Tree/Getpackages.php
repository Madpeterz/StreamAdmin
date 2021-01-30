<?php

namespace App\Endpoint\SecondLifeApi\Tree;

use App\R7\Set\PackageSet;
use App\R7\Model\Treevender;
use App\R7\Set\TreevenderpackagesSet;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Getpackages extends SecondlifeAjax
{
    protected function valueOrZero(string $value): string
    {
        if ($value !== null) {
            return $value;
        }
        return "0";
    }
    public function process(): void
    {
        $input = new InputFilter();
        $tree_vender_id = $input->postFilter("tree_vender_id", "integer");
        if ($tree_vender_id < 1) {
            $this->setSwapTag("message", "Invaild tree vender id given or none sent!");
            return;
        }
        $treevender = new Treevender();
        if ($treevender->loadID($tree_vender_id) == false) {
            $this->setSwapTag("message", "Unable to load selected tree vender");
            return;
        }
        $treevender_packages_set = new TreevenderpackagesSet();
        $load_status = $treevender_packages_set->loadOnField("treevenderLink", $treevender->getId());
        if ($load_status["status"] == false) {
            $this->setSwapTag("message", "Unable to load tree vender packages");
            return;
        }
        $package_set = new PackageSet();
        $load_status = $package_set->loadIds($treevender_packages_set->getUniqueArray("packageLink"));
        if ($load_status["status"] == false) {
            $this->setSwapTag("message", "Unable to load packages");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $reply = [];
        $reply["packageUid"] = [];
        $reply["package_autodj"] = [];
        $reply["package_autodjsize"] = [];
        $reply["package_listeners"] = [];
        $reply["package_bitrate"] = [];
        $reply["package_days"] = [];
        $reply["package_cost"] = [];
        $package_hashs = [];
        foreach ($treevender_packages_set->getAllIds() as $treevender_package_id) {
            $treevender_package = $treevender_packages_set->getObjectByID($treevender_package_id);
            $package = $package_set->getObjectByID($treevender_package->getPackageLink());
            $hash = sha1(implode(
                " ",
                [
                $package->getAutodj(),
                $package->getAutodjSize(),
                $package->getListeners(),
                $package->getBitrate(),
                $package->getDays(),
                $package->getCost(),
                ]
            ));
            if (in_array($hash, $package_hashs) == false) {
                $package_hashs[] = $hash;
                $reply["packageUid"][] = $package->getPackageUid();
                $reply["package_autodj"][] = [true => "Yes",false => "No"][$package->getAutodj()];
                $reply["package_autodjsize"][] = $this->valueOrZero($package->getAutodjSize());
                $reply["package_listeners"][] = $package->getListeners();
                $reply["package_bitrate"][] = $package->getBitrate();
                $reply["package_days"][] = $package->getDays();
                $reply["package_cost"][] = $package->getCost();
            }
        }
        foreach ($reply as $key => $value) {
            $this->setSwapTag($key, $value);
        }
    }
}
