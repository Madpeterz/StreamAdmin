<?php

namespace App\Endpoint\Secondlifeapi\Tree;

use App\Models\Treevender;
use App\Models\Set\ServertypesSet;
use App\Models\Set\StreamSet;
use App\Models\Set\TreevenderpackagesSet;
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
    public function processWithTreevenderID(int $tree_vender_id, bool $disableSoldoutChecks = true): void
    {
        $treevender = new Treevender();
        if ($treevender->loadID($tree_vender_id)->status == false) {
            $this->failed("Unable to load selected tree vender");
            return;
        }
        $this->setSwapTag("textureInuse", $treevender->_TextureInuse);
        $this->setSwapTag("textureWaiting", $treevender->_TextureWaiting);

        $treevender_packages_set = new TreevenderpackagesSet();
        $load_status = $treevender_packages_set->loadByTreevenderLink($treevender->_Id);
        if ($load_status->status == false) {
            $this->failed("Unable to load tree vender packages");
            return;
        }
        if ($treevender_packages_set->getCount() == 0) {
            $this->failed("No packages assigned to tree vender");
            return;
        }
        $package_set = $treevender_packages_set->relatedPackage();
        if ($package_set->getCount() != $treevender_packages_set->getCount()) {
            $this->failed("Incorrect number of packages loaded");
            return;
        }
        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();
        $this->ok("ok");

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
            $package = $package_set->getObjectByID($treevender_package->_PackageLink);
            $servertype = $servertypes_set->getObjectByID($package->_ServertypeLink);
            $skip = false;
            if (($treevender->_HideSoldout == true) && ($disableSoldoutChecks == false)) {
                $whereConfig = [
                    "fields" => ["packageLink","needWork","rentalLink"],
                    "values" => [$package->_Id,0,null],
                    "matches" => ["=","=","IS"],
                ];
                $stockLevel = $streamsSet->countInDB($whereConfig);
                if (($stockLevel->status == false) || ($stockLevel->items == 0)) {
                    $skip = true;
                }
            }
            if ($skip == true) {
                continue;
            }
            $hash = sha1(implode(
                " ",
                [
                $replaceServerTypes[$servertype->_Name],
                $package->_Autodj,
                $package->_AutodjSize,
                $package->_Listeners,
                $package->_Bitrate,
                $package->_Days,
                $package->_Cost,
                ]
            ));
            if (in_array($hash, $package_hashs) == false) {
                $package_hashs[] = $hash;
                $reply["package_servertype"][] = $replaceServerTypes[$servertype->_Name];
                $reply["packageUid"][] = $package->_PackageUid;
                $reply["package_autodj"][] = [true => "Yes",false => "No"][$package->_Autodj];
                $reply["package_autodjsize"][] = $this->valueOrZero($package->_AutodjSize);
                $reply["package_listeners"][] = $package->_Listeners;
                $reply["package_bitrate"][] = $package->_Bitrate;
                $reply["package_days"][] = $this->smeup($package->_Days);
                $reply["package_cost"][] = $package->_Cost;
            }
        }
        foreach ($reply as $key => $value) {
            $this->setSwapTag($key, $value);
        }
    }
    public function process(): void
    {

        $tree_vender_id = $this->input->post("tree_vender_id")->checkGrtThanEq(1)->asInt();
        if ($tree_vender_id === null) {
            $this->failed("Invaild tree vender id given or none sent!");
            return;
        }
        $this->processWithTreevenderID($tree_vender_id, false);
    }
    protected function smeup(int $input): string
    {
        $values = [
            1 => "24 hours",
            7 => "One week",
            14 => "Two week's",
            28 => "Four week's",
            31 => "Monthly",
            30 => "Monthly",
        ];
        if (in_array($input, $values) == true) {
            return $values[$input];
        }
        return $input . " day's";
    }
}
