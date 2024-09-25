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
        "values" => [$package->getId(),$treevender->getId()],
        "types" => ["i","i"],
        "matches" => ["=","="],
        ];
        if ($treevender_package->loadWithConfig($where_fields)->status == true) {
            $this->failed("This package is already assigend to this tree vender");
            $this->setSwapTag("redirect", "");
            return;
        }
        $treevender_package = new Treevenderpackages();
        $treevender_package->setPackageLink($package->getId());
        $treevender_package->setTreevenderLink($treevender->getId());
        $create_status = $treevender_package->createEntry();
        if ($create_status->status == false) {
            $this->failed("Unable to attach package to tree vender");
            return;
        }
        $this->redirectWithMessage("Package added to tree vender", "tree/manage/" . $treevender->getId());
        $this->createAuditLog(
            $treevender_package->getId(),
            "link package",
            "Tree:" . $treevender->getName(),
            "package: " . $package->getName()
        );
    }
}
