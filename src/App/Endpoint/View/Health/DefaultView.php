<?php

namespace App\Endpoint\View\Health;

use App\R7\Set\ObjectsSet;
use App\R7\Set\RegionSet;
use App\R7\Set\ResellerSet;

class DefaultView extends View
{
    public function process(): void
    {
        $this->owner_objects_list = [
            "apirequests",
            "mailserver",
            "noticeserver",
            "detailsserver",
            "notecardsserver",
            "clientautosuspendserver",
            "eventsserver",
            "botcommandqserver",
        ];
        $resellers = new ResellerSet();
        $resellers->loadAll();
        $venderHealth = new ObjectsSet();
        $whereConfig = [
            "fields" => ["avatarLink","objectMode"],
            "matches" => ["IN","NOT IN"],
            "values" => [$resellers->getUniqueArray("avatarLink"),$this->owner_objects_list],
            "types" => ["i","s"],
        ];
        $venderHealth->loadWithConfig($whereConfig);
        $regionsSet = new RegionSet();
        $regionsSet->loadByValues($venderHealth->getUniqueArray("regionLink"));
        $goodMinTime = time() - 120;
        $regions_report = [];
        foreach ($venderHealth as $object) {
            if (array_key_exists($object->getRegionLink(), $regions_report) == false) {
                $regions_report[$object->getRegionLink()] = [
                    "up" => 0,
                    "down" => 0,
                ];
            }
            if ($object->getLastSeen() >= $goodMinTime) {
                $regions_report[$object->getRegionLink()]["up"]++;
                continue;
            }
            $regions_report[$object->getRegionLink()]["down"]++;
        }

        $table_head = ["id","Region","Status","Percentage","Up","Down"];
        $table_body = [];
        foreach ($regions_report as $region_id => $dataset) {
            $region = $regionsSet->getObjectByID($region_id);
            if ($region == null) {
                continue;
            }
            $total = $dataset["up"] + $dataset["down"];
            $pcent = 0;
            if ($total > 0) {
                $pcent = ($dataset["up"] / $total) * 100;
            }
            $statustext = "Good";
            $statuscolor = "success";
            if ($pcent < 75) {
                $statustext = "Some down";
                $statuscolor = "warning";
            }
            if ($pcent < 50) {
                $statustext = "Alot down";
                $statuscolor = "danger";
            }
            if ($pcent < 5) {
                $statustext = "Totaly fucked";
                $statuscolor = "danger";
            }
            $entry = [];
            $entry[] = $region_id;
            $entry[] = "<a href=\"[[url_base]]health/detailed/"
            . $region->getId() . "\">" . $region->getName() . "</a>";
            $entry[] = "<span class=\"text-" . $statuscolor . "\">" . $statustext . "</span>";
            $entry[] = $pcent . "%";
            $entry[] = $dataset["up"];
            $entry[] = $dataset["down"];
            $table_body[] = $entry;
        }

        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body, 1));
    }
}
