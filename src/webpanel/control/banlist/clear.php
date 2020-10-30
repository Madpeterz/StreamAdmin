<?php
$banlist = new banlist();
$status = false;
$ajax_reply->set_swap_tag_string("redirect","banlist");
if($banlist->load($page) == true)
{
    $remove_status = $banlist->remove_me();
    if($remove_status["status"] == true)
    {
        $ajax_reply->set_swap_tag_string("message","entry removed");
    }
    else
    {
        $ajax_reply->set_swap_tag_string("message","Unable to remove entry");
    }
}
else
{
    $ajax_reply->set_swap_tag_string("message","unable to find entry");
}
?>
