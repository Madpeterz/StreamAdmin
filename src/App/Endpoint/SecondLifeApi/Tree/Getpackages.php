<?php

namespace App\Endpoint\SecondLifeApi\Tree;

use App\Models\Treevender;
use App\Models\Sets\ServertypesSet;
use App\Models\Sets\StreamSet;
use App\Models\Sets\TreevenderpackagesSet;
use App\Template\SecondlifeAjax;

class GetPackages extends SecondlifeAjax
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
        if ($treevender->loadID($tree_vender_id) == false) {
            $this->failed("Unable to load selected tree vender");
            return;
        }
        $this->setSwapTag("textureInuse", $treevender->getTextureInuse());
        $this->setSwapTag("textureWaiting", $treevender->getTextureWaiting());

        $treevender_packages_set = new TreevenderpackagesSet();
        $load_status = $treevender_packages_set->loadByTreevenderLink($treevender->getId());
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
