<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect","template");
$status = false;
if($accept == "Accept")
{
    $template = new template();
    if($template->load($page) == true)
    {
        $remove_status = $template->remove_me();
        if($remove_status["status"] == true)
        {
            $status = true;
            $ajax_reply->set_swap_tag_string("message",$lang["template.rm.info.1"]);
        }
        else
        {
            $ajax_reply->set_swap_tag_string("message",sprintf($lang["template.cr.error.6"],$remove_status["message"]));
        }
    }
    else
    {
        $ajax_reply->set_swap_tag_string("message",$lang["tempalte.rm.error.2"]);
    }
}
else
{
    $ajax_reply->set_swap_tag_string("message",$lang["tempalte.rm.error.1"]);
    $ajax_reply->set_swap_tag_string("redirect","template/manage/".$page."");
}
?>
