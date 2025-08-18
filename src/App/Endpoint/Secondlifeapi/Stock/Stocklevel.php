<?php

namespace App\Endpoint\Secondlifeapi\Stock;

use App\Models\Set\PackageSet;
use App\Models\Set\ServerSet;
use App\Models\Set\StreamSet;
use App\Template\SecondlifeAjax;

class Stocklevel extends SecondlifeAjax
{
    public function process(): void
    {
        $packages = new PackageSet();
        $load = $packages->loadAll();
        if($load->status == false)
        {
            $this->failed("Unable to load packages");
            return;
        }
        $servers = new ServerSet();
        $load = $servers->loadAll();
        if($load->status == false)
        {
            $this->failed("Unable to load servers");
            return;
        }
        $streams = new StreamSet();
        $streams->limitFields(["id","packageLink","serverLink"]);
        $whereConfig = [
            "fields" => ["rentalLink","needWork"],
            "values" => [null,0]
        ];
        $load = $streams->loadWithConfig($whereConfig);
        if($load->status == false)
        {
            $this->failed("Unable to load streams");
            return;
        }
        $emptyPackageSet = [];
        foreach($packages as $package)
        {
            $emptyPackageSet[$package->getId()] = [
                "name" => $package->getName(),
                "stock" => 0
            ];
        }
        $reply = [];
        foreach($servers as $server)
        {
            $reply[$server->getId()] = [
                "domain" => $server->getDomain(),
                "packages" => $emptyPackageSet,
            ];
        }
        foreach($streams as $stream)
        {
            $reply[$stream->getServerLink()]["packages"][$stream->getPackageLink()]["stock"] += 1;
        }
        $this->setSwapTag("results",$reply);
        $this->ok("see results");
    }
}
