<?php

$view_reply->set_swap_tag_string("page_actions", "<a href='[[url_base]]client/create'><button type='button' class='btn btn-success'>Create</button></a>");
if ($slconfig->get_clients_list_mode() == true) {
    include "webpanel/view/client/list.php";
} else {
    $view_reply->add_swap_tag_string("page_title", "Select a notice level");
    $notice_set = new notice_set();
    $notice_set->loadAll();
    $rental = new rental();
    $group_count = $sql->group_count($rental->get_table(), "noticelink");
    $table_head = array("id","NoticeLevel","Count");
    $table_body = [];
    if ($group_count["status"] == true) {
        foreach ($group_count["dataset"] as $key => $count) {
            $notice = $notice_set->get_object_by_id($key);
            $entry = [];
            $entry[] = $notice->get_id();
            $entry[] = '<a href="[[url_base]]client/bynoticelevel/' . $notice->get_id() . '">' . $notice->get_name() . '</a>';
            $entry[] = $count;
            $table_body[] = $entry;
        }
    }
    $view_reply->set_swap_tag_string("page_content", render_datatable($table_head, $table_body));
}
