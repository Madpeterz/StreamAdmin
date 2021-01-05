<?php

namespace App\Endpoints\Control\Tree;

use App\Models\Package;
use App\Models\Treevender;
use App\Models\Treevenderpackages;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Addpackage extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $package_id = $input->postFilter("package", "integer");

        $treevender = new Treevender();
        $this->output->setSwapTagString("redirect", "tree");
        if ($treevender->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find tree vender");
            return;
        }
        if ($package_id <= 0) {
            $this->output->setSwapTagString("message", "Unable to find package");
            return;
        }
        $package = new Package();
        if ($package->loadID($package_id) == false) {
            $this->output->setSwapTagString("message", "Unable to load package");
            return;
        }
        $treevender_package = new Treevenderpackages();
        $where_fields = [
        "fields" => ["packagelink","treevenderlink"],
        "values" => [$package->getId(),$treevender->getId()],
        "types" => ["i","i"],
        "matches" => ["=","="],
        ];
        if ($treevender_package->loadWithConfig($where_fields) == true) {
            $this->output->setSwapTagString("message", "This package is already assigend to this tree vender");
            $this->output->setSwapTagString("redirect", "");
            return;
        }
        $treevender_package = new Treevenderpackages();
        $treevender_package->setPackagelink($package->getId());
        $treevender_package->setTreevenderlink($treevender->getId());
        $create_status = $treevender_package->createEntry();
        if ($create_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to attach package to tree vender");
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("redirect", "tree/manage/" . $this->page . "");
        $this->output->setSwapTagString("message", "Package added to tree vender");
    }
}
