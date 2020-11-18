<?php

namespace App\View\Login;

use App\AvatarSet;
use App\ObjectsSet;
use App\RegionSet;

class DefaultView extends View
{
    public function process(): void
    {
        $objects_set = new ObjectsSet();
        $objects_set->loadAll();
        $region_set = new RegionSet();
        $region_set->loadIds($objects_set->getAllByField("regionlink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($objects_set->getAllByField("avatarlink"));

        $table_head = ["id","Object name","Script + Version","Last seen","Region","Object mode","Owner"];
        $table_body = [];
        if ($objects_set->getCount() == 0) {
            $template_parts["page_actions"] = "";
        }
        foreach ($objects_set->getAllIds() as $object_id) {
            $object = $objects_set->getObjectByID($object_id);
            $avatar = $avatar_set->getObjectByID($object->getAvatarlink());
            $region = $region_set->getObjectByID($object->getRegionlink());
            $entry = [];
            $entry[] = $object->getId();
            $bits = explode("S:", $object->getObjectname());
            if (count($bits) == 1) {
                $entry[] = $bits[0];
                $entry[] = "N/A";
            } else {
                $entry[] = $bits[0];
                $entry[] = $bits[1];
            }
            $entry[] = date('l jS \of F Y h:i:s A', $object->getLastseen());
            $tp_url = "http://maps.secondlife.com/secondlife/" . $region->getName() . "/"
            . implode("/", explode(",", $object->getObjectxyz())) . "";
            $tp_url = str_replace(' ', '%20', $tp_url);
            $entry[] = "<a href=\"" . $tp_url . "\" target=\"_blank\"><i class=\"fas fa-map-marked-alt\"></i> "
            . $region->getName() . "</a>";
            $entry[] = $object->getObjectxyz();
            $entry[] = $avatar->getAvatarname();
            $table_body[] = $entry;
        }
        $this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
    }
}
