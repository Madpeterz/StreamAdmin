<?php

namespace App\Endpoint\View\Package;

use App\R7\Set\PackageSet;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " All");
        $package_set = new PackageSet();
        $package_set->loadAll();

        $table_head = ["id","UID","Name","Listeners","Days","Kbps","Cost"];
        $table_body = [];

        foreach ($package_set->getAllIds() as $package_id) {
            $package = $package_set->getObjectByID($package_id);
            $entry = [];
            $entry[] = $package->getId();
            $entry[] = '<a href="[[url_base]]package/manage/' . $package->getPackageUid() . '">'
             . $package->getPackageUid() . '</a>';
            $entry[] = $package->getName();
            $entry[] = $package->getListeners();
            $entry[] = $package->getDays();
            $entry[] = $package->getBitrate();
            $entry[] = $package->getCost();
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
