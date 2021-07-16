<?php

namespace App\Endpoint\View\Objects;

use App\R7\Set\AvatarSet;
use App\R7\Set\ObjectsSet;
use App\R7\Set\RegionSet;

class DefaultView extends View
{
    public function process(): void
    {
        $objects_set = new ObjectsSet();
        $objects_set->loadAll();
        $region_set = new RegionSet();
        $region_set->loadIds($objects_set->getAllByField("regionLink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($objects_set->getAllByField("avatarLink"));

        $table_head = ["id","Object name","Script + Version","Last seen","Region","Object mode","Owner"];
        $table_body = [];
        if ($objects_set->getCount() == 0) {
            $template_parts["page_actions"] = "";
        }
        foreach ($objects_set->getAllIds() as $object_id) {
            $object = $objects_set->getObjectByID($object_id);
            $avatar = $avatar_set->getObjectByID($object->getAvatarLink());
            $region = $region_set->getObjectByID($object->getRegionLink());
            $entry = [];
            $entry[] = $object->getId();
            $bits = explode("S:", $object->getObjectName());
            if (count($bits) == 1) {
                $entry[] = $bits[0];
                $entry[] = "N/A";
            } else {
                $entry[] = $bits[0];
                $entry[] = $bits[1];
            }
            $entry[] = date('l jS \of F Y h:i:s A', $object->getLastSeen());
            $tp_url = "http://maps.secondlife.com/secondlife/" . $region->getName() . "/"
            . implode("/", explode(",", $object->getObjectXYZ())) . "";
            $tp_url = str_replace(' ', '%20', $tp_url);
            $entry[] = "<a href=\"" . $tp_url . "\" target=\"_blank\"><i class=\"fas fa-map-marked-alt\"></i> "
            . $region->getName() . "</a>";
            $entry[] = $object->getObjectXYZ();
            $entry[] = $avatar->getAvatarName();
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
