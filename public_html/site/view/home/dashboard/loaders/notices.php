<?php
$notice_set = new notice_set();
$notice_set->loadAll();
$client_expired = 0;
$client_expires_soon = 0;
$client_ok = 0;
$rental = new rental();
$group_count = $sql->group_count($rental->get_table(),"noticelink");
if($group_count["status"] == true)
{
    foreach($group_count["dataset"] as $key => $count)
    {
        $notice = $notice_set->get_object_by_id($key);
        if($notice->get_hoursremaining() <= 0) $client_expired+=$count;
        else if($notice->get_hoursremaining() > 24) $client_ok+=$count;
        else $client_expires_soon+=$count;
    }
}
?>
