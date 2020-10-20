<?php
$table_head = array("Object type","Count");
$table_body = array();
if($countdata_objects["status"] == true)
{
    foreach($countdata_objects["dataset"] as $key => $count)
    {
        $entry = array();
        $entry[] = $key;
        $entry[] = $count;
        $table_body[] = $entry;
    }
}
$sub_grid_objects = new grid();
$sub_grid_objects->add_content('<h4>Objects seen in the last hour</h4>',12);
$sub_grid_objects->add_content(render_table($table_head,$table_body),12);
?>
