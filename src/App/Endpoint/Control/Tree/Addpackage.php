<?php

namespace App\Endpoint\Control\Tree;

use App\Models\Package;
use App\Models\Treevender;
use App\Models\Treevenderpackages;
use App\Framework\ViewAjax;

class Addpackage extends ViewAjax
{
    public function process(): void
    {

        $package_id = $this->post("package", "integer");

        $treevender = new Treevender();
        $this->setSwapTag("redirect", "tree");
        if ($treevender->loadID($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to find tree vender");
            return;
        }
        if ($package_id <= 0) {
            $this->failed("Unable to find package");
            return;
        }
        $package = new Package();
        if ($package->loadID($package_id) == false) {
            $this->failed("Unable to load package");
            return;
        }
        $treevender_package = new Treevenderpackages();
        $where_fields = [
        "fields" => ["packageLink","treevenderLink"],
        "values" => [$package->getId(),$treevender->getId()],
        "types" => ["i","i"],
        "matches" => ["=","="],
        ];
        if ($treevender_package->loadWithConfig($where_fields) == true) {
            $this->failed("This package is already assigend to this tree vender");
            $this->setSwapTag("redirect", "");
            return;
        }
        $treevender_package = new Treevenderpackages();
        $treevender_package->setPackageLink($package->getId());
        $treevender_package->setTreevenderLink($treevender->getId());
        $create_status = $treevender_package->createEntry();
        if ($create_status["status"] == false) {
            $this->failed("Unable to attach package to tree vender");
            return;
        }
        $this->ok("Package added to tree vender");
        $this->setSwapTag("redirect", "tree/manage/" . $treevender->getId() . "");
    }
}
