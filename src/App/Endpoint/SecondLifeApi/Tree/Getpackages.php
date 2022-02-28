<?php

namespace App\Endpoint\SecondLifeApi\Tree;

use App\Models\Sets\PackageSet;
use App\Models\Treevender;
use App\Models\Sets\ServertypesSet;
use App\Models\Sets\StreamSet;
use App\Models\Sets\TreevenderpackagesSet;
use App\Template\SecondlifeAjax;

class Getpackages extends SecondlifeAjax
{
    protected function valueOrZero(?string $value): string
    {
        if ($value !== null) {
            return $value;
        }
        return "0";
    }
    public function processWithTreevenderID($tree_vender_id, bool $disableSoldoutChecks = true): void
    {
        $treevender = new Treevender();
        if ($treevender->loadID($tree_vender_id) == false) {
            $this->setSwapTag("message", "Unable to load selected tree vender");
            return;
        }
        $this->setSwapTag("textureInuse", $treevender->getTextureInuse());
        $this->setSwapTag("textureWaiting", $treevender->getTextureWaiting());

        $treevender_packages_set = new TreevenderpackagesSet();
        $load_status = $treevender_packages_set->loadOnField("treevenderLink", $treevender->getId());
        if ($load_status["status"] == false) {
            $this->setSwapTag("message", "Unable to load tree vender packages");
            return;
        }
        $package_set = new PackageSet();
        $load_status = $package_set->loadByValues($treevender_packages_set->getUniqueArray("packageLink"));
        if ($load_status["status"] == false) {
            $this->setSwapTag("message", "Unable to load packages");
            return;
        }
        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();

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
        $reply["package_servertype"] = [];

        $replaceServerTypes = [
            "Icecast" => "A",
            "ShoutcastV1" => "B",
            "ShoutcastV2" => "C",
        ];
        $streamsSet = new StreamSet();
        $package_hashs = [];
        foreach ($treevender_packages_set->getAllIds() as $treevender_package_id) {
            $treevender_package = $treevender_packages_set->getObjectByID($treevender_package_id);
            $package = $package_set->getObjectByID($treevender_package->getPackageLink());
            $servertype = $servertypes_set->getObjectByID($package->getServertypeLink());
            $skip = false;
            if (($treevender->getHideSoldout() == true) && ($disableSoldoutChecks == false)) {
                $whereConfig = [
                    "fields" => ["packageLink","needWork","rentalLink"],
                    "values" => [$package->getId(),0,null],
                    "matches" => ["=","=","IS"],
                ];
                $stockLevel = $streamsSet->countInDB($whereConfig);
                if (($stockLevel === null) || ($stockLevel == 0)) {
                    $skip = true;
                }
            }
            if ($skip == true) {
                continue;
            }
            $hash = sha1(implode(
                " ",
                [
                $replaceServerTypes[$servertype->getName()],
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
                $reply["package_servertype"][] = $replaceServerTypes[$servertype->getName()];
                $reply["packageUid"][] = $package->getPackageUid();
                $reply["package_autodj"][] = [true => "Yes",false => "No"][$package->getAutodj()];
                $reply["package_autodjsize"][] = $this->valueOrZero($package->getAutodjSize());
                $reply["package_listeners"][] = $package->getListeners();
                $reply["package_bitrate"][] = $package->getBitrate();
                $reply["package_days"][] = $this->smeup($package->getDays());
                $reply["package_cost"][] = $package->getCost();
            }
        }
        foreach ($reply as $key => $value) {
            $this->setSwapTag($key, $value);
        }
    }
    public function process(): void
    {

        $tree_vender_id = $this->post("tree_vender_id", "integer");
        if ($tree_vender_id < 1) {
            $this->setSwapTag("message", "Invaild tree vender id given or none sent!");
            return;
        }
        $this->processWithTreevenderID($tree_vender_id, false);
    }
    protected function smeup(int $input): string
    {
        if ($input > 1) {
            if ($input == 7) {
                return "One week";
            } elseif ($input == 14) {
                return "Two week's";
            } elseif ($input == 21) {
                return "Three week's";
            } elseif ($input == 28) {
                return "Four week's";
            } elseif ($input == 31) {
                return "Monthly";
            } elseif ($input == 30) {
                return "Monthly";
            }
            return $input . " day's";
        }
        return "24 hours";
    }
}
