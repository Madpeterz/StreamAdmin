<?php

namespace App\Endpoint\Control\Tree;

use App\R7\Model\Package;
use App\R7\Model\Treevender;
use App\R7\Model\Treevenderpackages;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Addpackage extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $package_id = $input->postFilter("package", "integer");

        $treevender = new Treevender();
        $this->setSwapTag("redirect", "tree");
        if ($treevender->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to find tree vender");
            return;
        }
        if ($package_id <= 0) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        $package = new Package();
        if ($package->loadID($package_id) == false) {
            $this->setSwapTag("message", "Unable to load package");
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
            $this->setSwapTag("message", "This package is already assigend to this tree vender");
            $this->setSwapTag("redirect", "");
            return;
        }
        $treevender_package = new Treevenderpackages();
        $treevender_package->setPackageLink($package->getId());
        $treevender_package->setTreevenderLink($treevender->getId());
        $create_status = $treevender_package->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag("message", "Unable to attach package to tree vender");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("redirect", "tree/manage/" . $treevender->getId() . "");
        $this->setSwapTag("message", "Package added to tree vender");
    }
}
