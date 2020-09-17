<?php
$table_head = array("ordering","Name","Use bot","Hours remaining");
$table_body = array();
$notice_set = new notice_set();
$notice_set->loadAll();

foreach($notice_set->get_all_ids() as $notice_id)
{
    $notice = $notice_set->get_object_by_id($notice_id);
    if($notice->get_hoursremaining() != 999)
    {
        $entry = array();
        $entry[] = $notice->get_hoursremaining();
        $entry[] = '<a href="[[url_base]]notice/manage/'.$notice->get_id().'">'.$notice->get_name().'</a>';
        $entry[] = array(false=>"No",true=>"Yes")[$notice->get_usebot()];
        $entry[] = $notice->get_hoursremaining();
        $table_body[] = $entry;
    }
}
print render_datatable($table_head,$table_body);
?>
