<?php
$objects_set = new objects_set();
$objects_set->loadAll();
$region_set = new region_set();
$region_set->load_ids($objects_set->get_all_by_field("regionlink"));
$avatar_set = new avatar_set();
$avatar_set->load_ids($objects_set->get_all_by_field("avatarlink"));

$table_head = array("id","Object name","Script + Version","Last seen","Region","Object mode","Owner");
$table_body = array();
if($objects_set->get_count() == 0)
{
    $template_parts["page_actions"] = "";
}
foreach($objects_set->get_all_ids() as $object_id)
{
    $object = $objects_set->get_object_by_id($object_id);
    $avatar = $avatar_set->get_object_by_id($object->get_avatarlink());
    $region = $region_set->get_object_by_id($object->get_regionlink());
    $entry = array();
    $entry[] = $object->get_id();
    $bits = explode("S:",$object->get_objectname());
    if(count($bits) == 1)
    {
        $entry[] = $bits[0];
        $entry[] = "N/A";
    }
    else
    {
        $entry[] = $bits[0];
        $entry[] = $bits[1];
    }
    $entry[] = date('l jS \of F Y h:i:s A',$object->get_lastseen());
    $tp_url = urlencode("http://maps.secondlife.com/secondlife/".$region->get_name()."/".implode("/",explode(",",$object->get_objectxyz()))."");
    $entry[] = "<a href=\"".$tp_url."\" target=\"_blank\">".$region->get_name()."</a>";
    $entry[] = $object->get_objectmode();
    $entry[] = $avatar->get_avatarname();
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
