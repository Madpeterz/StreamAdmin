<?php

namespace App\Endpoint\View\Stream;

use App\Models\Set\PackageSet;
use App\Models\Set\StreamSet;

class DefaultView extends View
{
    protected function listPackages(): void
    {
        $this->output->addSwapTagString("page_title", " Please select a package");
        $package_set = new PackageSet();
        $package_set->loadAll();
        $stream_set = new StreamSet();
        $stream_set->limitFields(["rentalLink","needWork","packageLink"]);
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
            $entry[] = '<a href="[[SITE_URL]]stream/inpackage/' . $package->getPackageUid() . '">'
            . $package->getName() . '</a>';
            $entry[] = $streams_in_package[$package->getId()]["sold"];
            $entry[] = $streams_in_package[$package->getId()]["work"];
            $entry[] = $streams_in_package[$package->getId()]["ready"];
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body, 5));
    }
    protected function listServers(): void
    {
        $this->output->addSwapTagString("page_title", " Please select a server");
        $stream_set = new StreamSet();
        $stream_set->limitFields(["rentalLink","needWork","serverLink"]);
        $stream_set->loadAll();
        $server_set = $stream_set->relatedServer();
        $streams_in_server = [];
        foreach ($server_set->getAllIds() as $serverid) {
            $streams_in_server[$serverid] = ["sold" => 0,"work" => 0,"ready" => 0];
        }
        foreach ($stream_set as $stream) {
            if ($stream->getRentalLink() != null) {
                $streams_in_server[$stream->getServerLink()]["sold"]++;
                continue;
            }
            if ($stream->getNeedWork() == false) {
                $streams_in_server[$stream->getServerLink()]["ready"]++;
                continue;
            }
            $streams_in_server[$stream->getServerLink()]["work"]++;
        }

        $table_head = ["id","Name","Sold","Need work","Ready"];
        $table_body = [];
        foreach ($server_set as $server) {
            $entry = [];
            $entry[] = $server->getId();
            $entry[] = '<a href="[[SITE_URL]]stream/Onserver/' . $server->getId() . '">'
            . $server->getDomain() . '</a>';
            $entry[] = $streams_in_server[$server->getId()]["sold"];
            $entry[] = $streams_in_server[$server->getId()]["work"];
            $entry[] = $streams_in_server[$server->getId()]["ready"];
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body, 5));
    }
    public function process(): void
    {

        if ($this->siteConfig->getSlConfig()->getStreamListOption() == 1) { // list by server
            $this->listServers();
            return;
        }
        $this->listPackages();
        return;
    }
}
