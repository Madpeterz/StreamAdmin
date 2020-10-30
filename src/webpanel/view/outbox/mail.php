<?php
$view_reply->add_swap_tag_string("page_title"," Unsent mail");
$table_head = array("id","Avatar name","Start of message");
$table_body = [];
$message_set = new message_set();
$message_set->loadAll();
$avatar_set = new avatar_set();
$avatar_set->load_ids($message_set->get_all_by_field("avatarlink"));
foreach($message_set->get_all_ids() as $message_id)
{
    $message = $message_set->get_object_by_id($message_id);
    $avatar = $avatar_set->get_object_by_id($message->get_avatarlink());
    $message_content = $message->get_message();
    if(strlen($message_content) > 24) $message_content = substr($message_content,0,24)." ...";
    $table_body[] = array($message->get_id(),$avatar->get_avatarname(),$message_content);
}
$view_reply->set_swap_tag_string("page_content",render_datatable($table_head,$table_body));
?>
