<?php

namespace App\Endpoints\View\Stream;

use App\Models\PackageSet;
use App\Models\StreamSet;

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
        foreach ($stream_set->getAllIds() as $stream_id) {
            $stream = $stream_set->getObjectByID($stream_id);
            if ($stream->getRentallink() == null) {
                if ($stream->getNeedwork() == false) {
                    $streams_in_package[$stream->getPackagelink()]["ready"]++;
                } else {
                    $streams_in_package[$stream->getPackagelink()]["work"]++;
                }
            } else {
                $streams_in_package[$stream->getPackagelink()]["sold"]++;
            }
        }

        $table_head = ["id","Name","Sold","Need work","Ready"];
        $table_body = [];

        foreach ($package_set->getAllIds() as $package_id) {
            $package = $package_set->getObjectByID($package_id);
            $entry = [];
            $entry[] = $package->getId();
            $entry[] = '<a href="[[url_base]]stream/inpackage/' . $package->getPackage_uid() . '">'
            . $package->getName() . '</a>';
            $entry[] = $streams_in_package[$package_id]["sold"];
            $entry[] = $streams_in_package[$package_id]["work"];
            $entry[] = $streams_in_package[$package_id]["ready"];
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", render_datatable($table_head, $table_body));
    }
}
