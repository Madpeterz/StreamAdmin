<?php
$staff_set = new staff_set();
$staff_set->loadAll();
$table_head = array("id","Username","Owner");
$table_body = array();
foreach($staff_set->get_all_ids() as $staff_id)
{
    $staff = $staff_set->get_object_by_id($staff_id);
    $entry = array();
    $entry[] = $staff->get_id();
    if($session->get_ownerlevel() == true)
    {
        $entry[] = '<a href="[[url_base]]staff/manage/'.$staff->get_id().'">'.$staff->get_username().'</a>';
    }
    else $entry[] = $staff->get_username();
    $entry[] = array(false=>"No",true=>"Yes")[$staff->get_ownerlevel()];
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
