<?php
if($slconfig->get_clients_list_mode() == true)
{
    include("site/view/client/list.php");
}
else
{
    $template_parts["page_title"] .= " Select a notice level";
    $notice_set = new notice_set();
    $notice_set->loadAll();
    $rental = new rental();
    $group_count = $sql->group_count($rental->get_table(),"noticelink");
    $table_head = array("id","NoticeLevel","Count");
    $table_body = array();
    if($group_count["status"] == true)
    {
        foreach($group_count["dataset"] as $key => $count)
        {
            $notice = $notice_set->get_object_by_id($key);
            $entry = array();
            $entry[] = $notice->get_id();
            $entry[] = '<a href="[[url_base]]client/bynoticelevel/'.$notice->get_id().'">'.$notice->get_name().'</a>';
            $entry[] = $count;
            $table_body[] = $entry;
        }
    }
    print render_datatable($table_head,$table_body);
}
?>
