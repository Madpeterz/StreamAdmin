<?php

namespace App\Endpoint\View\Server;

use App\Models\Server;
use App\Models\Sets\PackageSet;
use App\Models\Sets\ServerSet;
use App\Models\Sets\StreamSet;

class DefaultView extends View
{
    public function process(): void
    {
        $table_head = [
            "id",
            "Domain",
            "Ip",
            "Bandwith -> Assigned | In use",
            "Storage -> Assigned | In use",
        ];
        $table_body = [];
        $server_set = new ServerSet();
        $server_set->loadAll();
        $stream_set = new StreamSet();
        $stream_set->limitFields(["id", "serverLink", "packageLink", "rentalLink"]);
        $stream_set->loadAll();
        $package_set = new PackageSet();
        $package_set->limitFields(["id", "autodjSize", "listeners", "bitrate"]);
        $package_set->loadAll();

        $serverDetails = [];
        foreach ($server_set as $server) {
            $serverDetails[$server->getId()] = [
                "bandwith_assigned" => 0,
                "bandwith_inuse" => 0,
                "storage_assigned" => 0,
                "storage_inuse" => 0,
            ];
        }

        foreach ($stream_set as $stream) {
            $package = $package_set->getObjectByID($stream->getPackageLink());
            $bandwidth_cost = $package->getBitrate() * $package->getListeners();
            $storage_cost = $package->getAutodjSize();
            $serverid = $stream->getServerLink();
            $serverDetails[$serverid]["bandwith_assigned"] += $bandwidth_cost;
            $serverDetails[$serverid]["storage_assigned"] += $storage_cost;
            if ($stream->getRentalLink() != null) {
                $serverDetails[$serverid]["bandwith_inuse"] += $bandwidth_cost;
                $serverDetails[$serverid]["storage_inuse"] += $storage_cost;
            }
        }
        foreach ($server_set as $server) {
            $serverip = $server->getIpaddress();
            if ($serverip == null) {
                $serverip = "Not set";
            }
            $serverid = $server->getId();
            $entry = [];
            $entry[] = $serverid;
            $entry[] = '<a href="[[SITE_URL]]server/manage/' . $serverid . '">' . $server->getDomain() . '</a>';
            $entry[] = $serverip;
            $serverbandwithkb = $this->convertBandwithTokbps($server);
            $inuse_bandwidth_percent = round(
                ($serverDetails[$serverid]["bandwith_inuse"] / $serverbandwithkb) * 100,
                2
            );
            $assigned_bandwidth_percent = round(
                ($serverDetails[$serverid]["bandwith_assigned"] / $serverbandwithkb) * 100,
                2
            );
            $entry[] = $assigned_bandwidth_percent . "% | " . $inuse_bandwidth_percent . "%";
            $serverstoragemb = $this->convertStorageToMb($server);
            $inuse_storage_percent = round(
                ($serverDetails[$serverid]["storage_inuse"] / $serverstoragemb) * 100,
                2
            );
            $assigned_storage_percent = round(
                ($serverDetails[$serverid]["storage_assigned"] / $serverstoragemb) * 100,
                2
            );
            $entry[] = $assigned_storage_percent . "% | " . $inuse_storage_percent . "%";
            $table_body[] = $entry;
        }
        $this->addLib("chartjs");
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }

    protected function convertStorageToMb(Server $server): int
    {
        if ($server->getTotalStorageType() == "mb") {
            return $server->getTotalStorage();
        } elseif ($server->getTotalStorageType() == "gb") {
            return ($server->getTotalStorage() * 1000);
        }
        return (($server->getTotalStorage() * 1000) * 1000);
    }

    protected function convertBandwithTokbps(Server $server): int
    {
        if ($server->getBandwidthType() == "kbps") {
            return $server->getBandwidth();
        } elseif ($server->getBandwidthType() == "mbps") {
            return ($server->getBandwidth() * 1000);
        }
        return (($server->getBandwidth() * 1000) * 1000);
    }
}
