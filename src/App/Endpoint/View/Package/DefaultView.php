<?php

namespace App\Endpoint\View\Package;

use App\Models\Set\PackageSet;
use App\Models\Set\ServertypesSet;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " All");
        $package_set = new PackageSet();
        $package_set->loadAll();
        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();

        $table_head = ["id","UID","Name","Type","Listeners","Days","Kbps","Cost","AutoDJ [GB]"];
        $table_body = [];

        foreach ($package_set as $package) {
            $type = $servertypes_set->getObjectByID($package->_ServertypeLink);
            $entry = [];
            $entry[] = $package->_Id;
            $entry[] = '<a href="[[SITE_URL]]package/manage/' . $package->_PackageUid . '">'
             . $package->_PackageUid . '</a>';
            $entry[] = $package->_Name;
            $entry[] = $type->_Name;
            $entry[] = $package->_Listeners;
            $entry[] = $package->_Days;
            $entry[] = $package->_Bitrate;
            $entry[] = $package->_Cost;
            $autoDJ = "No";
            if ($package->_Autodj == true) {
                $autoDJ = $package->_AutodjSize;
            }
            $entry[] = $autoDJ;
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body, 6));
    }
}
