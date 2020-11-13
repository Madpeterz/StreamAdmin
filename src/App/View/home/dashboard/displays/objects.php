<?php

$seen_objects = [];
$table_head = array("Object type","Last connected","Region");
$table_body = [];
$all_good = true;
$issues = 0;
foreach ($objects_set->get_all_ids() as $object_id) {
    $object = $objects_set->get_object_by_id($object_id);
    $region = $region_set->get_object_by_id($object->get_regionlink());
    $entry = [];
    $color = "text-light";
    if (in_array($object->get_objectmode(), $seen_objects) == true) {
        $color = "text-danger";
        $issues++;
    } else {
        $seen_objects[] = $object->get_objectmode();
    }
    $entry[] = '<span class="' . $color . '">' . str_replace("server", "", $object->get_objectmode()) . '</span>';
    $color = "text-success";
    $dif = time() - $object->get_lastseen();
    if ($dif > 240) {
        $issues += 5;
        $color = "text-danger";
    } elseif ($dif > 65) {
        $issues++;
        $color = "text-warning";
    }
    $entry[] = '<span class="' . $color . '">' . expired_ago($object->get_lastseen(), true) . '</span>';
    $tp_url = "http://maps.secondlife.com/secondlife/" . $region->get_name() . "/" . implode("/", explode(",", $object->get_objectxyz())) . "";
    $tp_url = str_replace(' ', '%20', $tp_url);
    $entry[] = "<a href=\"" . $tp_url . "\" target=\"_blank\"><i class=\"fas fa-map-marked-alt\"></i> " . $region->get_name() . "</a>";
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
$sub_grid_objects = new grid();
$issues_badge = "";
if ($issues == 0) {
    $issues_badge = '<span class="badge badge-success"><i class="fas fa-check-square"></i></span>';
} elseif ($issues > 3) {
    $issues_badge = '<span class="badge badge-danger"><i class="fas fa-burn"></i></span>';
} else {
    $issues_badge = '<span class="badge badge-warning"><i class="far fa-caret-square-right"></i></span>';
}
$sub_grid_objects->add_content('<h4>SL health ' . $issues_badge . '</h4>', 12);
$sub_grid_objects->add_content(render_table($table_head, $table_body, "", false), 12);
