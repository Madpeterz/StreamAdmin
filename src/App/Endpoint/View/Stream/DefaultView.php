<?php

namespace App\Endpoint\View\Stream;

use App\R7\Set\PackageSet;
use App\R7\Set\StreamSet;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Please select a package");
        $package_set = new PackageSet();
        $package_set->loadAll();
        $stream_set = new StreamSet();
        $stream_set->loadAll();

        $streams_in_package = [];
        foreach ($package_set->getAllIds() as $package_id) {
            $streams_in_package[$package_id] = ["sold" => 0,"work" => 0,"ready" => 0];
        }
        foreach ($stream_set as $stream) {
            if ($stream->getRentalLink() != null) {
                $streams_in_package[$stream->getPackageLink()]["sold"]++;
                continue;
            }
            if ($stream->getNeedWork() == false) {
                $streams_in_package[$stream->getPackageLink()]["ready"]++;
                continue;
            }
            $streams_in_package[$stream->getPackageLink()]["work"]++;
        }

        $table_head = ["id","Name","Sold","Need work","Ready"];
        $table_body = [];

        foreach ($package_set as $package) {
            $entry = [];
            $entry[] = $package->getId();
            $entry[] = '<a href="[[url_base]]stream/inpackage/' . $package->getPackageUid() . '">'
            . $package->getName() . '</a>';
            $entry[] = $streams_in_package[$package->getId()]["sold"];
            $entry[] = $streams_in_package[$package->getId()]["work"];
            $entry[] = $streams_in_package[$package->getId()]["ready"];
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body, 5));
    }
}
