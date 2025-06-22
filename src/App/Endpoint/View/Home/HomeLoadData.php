<?php

namespace App\Endpoint\View\Home;

use App\Models\Botconfig;
use App\Models\Set\NoticeSet;
use App\Models\Set\ObjectsSet;
use App\Models\Set\RegionSet;
use App\Models\Rental;
use App\Models\Set\ResellerSet;
use App\Models\Set\ServerSet;
use App\Models\Set\StreamSet;
use YAPF\Bootstrap\Template\Grid;

abstract class HomeLoadData extends View
{
    protected $client_expired = 0;
    protected $client_expires_soon = 0;
    protected $client_ok = 0;
    protected RegionSet $region_set;
    protected ObjectsSet $objects_set;
    protected ?ServerSet $server_set = null;
    protected $server_loads = [];
    protected $owner_objects_list = [];
    protected Grid $main_grid;
    protected $stream_total_sold = 0;
    protected $stream_total_ready = 0;
    protected $stream_total_needWork = 0;
    protected StreamSet $stream_set;

    protected $venderHealthGood = 0;
    protected $venderHealthBad = 0;

    protected function loadDatasets(): void
    {
        $this->loadServers();
        $this->loadServerLoads();
        $this->loadObjects();
        $this->loadNotices();
        $this->loadStreamStatus();
        $this->loadVenderHealth();
    }

    protected function loadVenderHealth(): void
    {
        $this->loadOwnerObjects();
        $resellers = new ResellerSet();
        $resellers->loadAll();
        $venderHealth = new ObjectsSet();
        $whereConfig = [
            "fields" => ["avatarLink","objectMode"],
            "matches" => ["IN","NOT IN"],
            "values" => [$resellers->uniqueAvatarLinks(),$this->owner_objects_list],
            "types" => ["i","s"],
        ];
        $venderHealth->loadWithConfig($whereConfig);
        if ($venderHealth->getCount() == 0) {
            $this->venderHealthGood = 1;
            $this->venderHealthBad = 0;
            return;
        }
        $goodMinTime = time() - 120;
        foreach ($venderHealth as $object) {
            if ($object->getLastSeen() >= $goodMinTime) {
                $this->venderHealthGood++;
                continue;
            }
            $this->venderHealthBad++;
        }
    }

    protected function loadServers(): void
    {
        $this->server_set = new ServerSet();
        $this->server_set->loadAll();
    }

    protected function loadStreamStatus(): void
    {
        $this->stream_set = new StreamSet();
        $this->stream_set->loadAll();
        foreach ($this->stream_set as $stream) {
            if ($stream->getRentalLink() !== null) {
                $this->stream_total_sold++;
                $this->server_loads[$stream->getServerLink()]["sold"]++;
                continue;
            }
            if ($stream->getNeedWork() == false) {
                $this->stream_total_ready++;
                $this->server_loads[$stream->getServerLink()]["ready"]++;
                continue;
            }
            $this->stream_total_needWork++;
            $this->server_loads[$stream->getServerLink()]["needWork"]++;
        }
    }

    protected function loadServerLoads(): void
    {
        $this->server_loads = [];
        foreach ($this->server_set->getAllIds() as $server_id) {
            $this->server_loads[$server_id] = ["ready" => 0,"sold" => 0,"needWork" => 0];
        }
    }

    protected function loadOwnerObjects(): void
    {
        if (count($this->owner_objects_list) > 0) {
            return;
        }
        $this->owner_objects_list = [
            "mailserver",
            "noticeserver",
            "detailsserver",
        ];

        $botconfig = new Botconfig();
        $botconfig->loadID(1);
        $bits = [$botconfig->getIms(),$botconfig->getInvites(),$botconfig->getNotecards()];
        if (in_array(true, $bits) == true) {
            $this->owner_objects_list[] = "botcommandqserver";
            if ($botconfig->getNotecards() == true) {
                $this->owner_objects_list[] = "notecardsserver";
            }
        }

        if ($this->server_set == null) {
            $this->loadServers();
        }

        if ($this->siteConfig->getSlConfig()->getEventsAPI() == true) {
            $this->owner_objects_list[] = "eventsserver";
        }
    }

    protected function loadObjects(): void
    {
        $this->loadOwnerObjects();
        $one_hour_ago = (time() - $this->siteConfig->unixtimeHour());
        $this->objects_set = new ObjectsSet();
        $where_config = [
        "fields" => ["avatarLink","lastSeen","objectMode"],
        "matches" => ["=",">=","IN"],
        "values" => [$this->siteConfig->getSlConfig()->getOwnerAvatarLink(),$one_hour_ago,$this->owner_objects_list],
        "types" => ["i","i","s"],
        ];
        $order_config = [
            "ordering_enabled" => true,
            "order_field" => "id",
            "order_dir" => "DESC",
        ];
        $this->objects_set->loadWithConfig($where_config, $order_config);
        $this->region_set = new RegionSet();
        $this->region_set->loadAll();
    }
    protected function loadNotices(): void
    {
        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $rental = new Rental();
        $group_count = $this->siteConfig->getSQL()->groupCountV2($rental->getTable(), "noticeLink");
        if ($group_count->status == true) {
            foreach ($group_count->dataset as $entry) {
                if ($entry["items"] <= 0) {
                    continue;
                }
                $notice = $notice_set->getObjectByID($entry["noticeLink"]);
                if ($notice->getHoursRemaining() <= 0) {
                    $this->client_expired += $entry["items"];
                    continue;
                }
                if ($notice->getHoursRemaining() < 24) {
                    $this->client_expires_soon += $entry["items"];
                    continue;
                }
                $this->client_ok += $entry["items"];
            }
        }
    }
}
