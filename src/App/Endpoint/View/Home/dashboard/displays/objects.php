<?php

use App\Template\Grid;

$seen_objects = [];
$table_head = ["Object type","Last connected","Region"];
$table_body = [];
$all_good = true;
$issues = 0;
foreach ($objects_set->getAllIds() as $object_id) {
    $object = $objects_set->getObjectByID($object_id);
    $region = $region_set->getObjectByID($object->getRegionLink());
    $entry = [];
    $color = "text-light";
    if (in_array($object->get_objectMode(), $seen_objects) == true) {
        $color = "text-danger";
        $issues++;
    } else {
        $seen_objects[] = $object->get_objectMode();
    }
    $entry[] = '<span class="' . $color . '">' . str_replace("server", "", $object->get_objectMode()) . '</span>';
    $color = "text-success";
    $dif = time() - $object->get_lastSeen();
    if ($dif > 240) {
        $issues += 5;
        $color = "text-danger";
    } elseif ($dif > 65) {
        $issues++;
        $color = "text-warning";
    }
    $entry[] = '<span class="' . $color . '">' . expiredAgo($object->get_lastSeen(), true) . '</span>';
    $tp_url = "http://maps.secondlife.com/secondlife/" . $region->getName() . "/"
     . implode("/", explode(",", $object->get_objectXYZ())) . "";
    $tp_url = str_replace(' ', '%20', $tp_url);
    $entry[] = "<a href=\"" . $tp_url . "\" target=\"_blank\"><i class=\"fas fa-map-marked-alt\"></i> "
    . $region->getName() . "</a>";
    $table_body[] = $entry;
}
foreach ($owner_objects_list as $objecttype) {
    if (in_array($objecttype, $seen_objects) == false) {
        $issues += 5;
        $entry = [];
        $entry[] = $objecttype;
        $entry[] = "<span class=\"text-warning\">Not connected in the last hour!</span>";
        $entry[] = "/";
        $table_body[] = $entry;
    }
}
$sub_grid_objects = new Grid();
$issues_badge = "";
if ($issues == 0) {
    $issues_badge = '<span class="badge badge-success"><i class="fas fa-check-square"></i></span>';
} elseif ($issues > 3) {
    $issues_badge = '<span class="badge badge-danger"><i class="fas fa-burn"></i></span>';
} else {
    $issues_badge = '<span class="badge badge-warning"><i class="far fa-caret-square-right"></i></span>';
}
$sub_grid_objects->addContent('<h4>SL health ' . $issues_badge . '</h4>', 12);
$sub_grid_objects->addContent($this->renderTable($table_head, $table_body, "", false), 12);
