<?php

namespace App\Endpoint\Control\Tree;

use App\Models\Package;
use App\Models\Treevender;
use App\Models\Treevenderpackages;
use App\Template\ControlAjax;

class Addpackage extends ControlAjax
{
    public function process(): void
    {
        $package_id = $this->input->post("package")->checkGrtThanEq(1)->asInt();
        if ($package_id == null) {
            $this->failed("Unable to find package");
            return;
        }

        $treevender = new Treevender();
        $this->setSwapTag("redirect", "tree");
        if ($treevender->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find tree vender");
            return;
        }

        $package = new Package();
        if ($package->loadID($package_id)->status == false) {
            $this->failed("Unable to load package");
            return;
        }
        $treevender_package = new Treevenderpackages();
        $where_fields = [
        "fields" => ["packageLink","treevenderLink"],
        "values" => [$package->_Id,$treevender->_Id],
        "types" => ["i","i"],
        "matches" => ["=","="],
        ];
        if ($treevender_package->loadWithConfig($where_fields)->status == true) {
            $this->failed("This package is already assigend to this tree vender");
            $this->setSwapTag("redirect", "");
            return;
        }
        $treevender_package = new Treevenderpackages();
        $treevender_package->_PackageLink = $package->_Id;
        $treevender_package->_TreevenderLink = $treevender->_Id;
        $create_status = $treevender_package->createEntry();
        if ($create_status->status == false) {
            $this->failed("Unable to attach package to tree vender");
            return;
        }
        $this->redirectWithMessage("Package added to tree vender", "tree/manage/" . $treevender->_Id);
        $this->createAuditLog(
            $treevender_package->_Id,
            "link package",
            "Tree:" . $treevender->_Name,
            "package: " . $package->_Name
        );
    }
}
