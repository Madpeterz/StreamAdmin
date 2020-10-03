<?php
$input = new inputFilter();
$name = $input->postFilter("name");
$detail = $input->postFilter("detail");
$notecarddetail = $input->postFilter("notecarddetail");
$failed_on = "";
if(strlen($name) < 5) $failed_on .= $lang["template.cr.error.1"];
else if(strlen($name) > 30) $failed_on .= $lang["template.cr.error.2"];
else if(strlen($detail) < 5) $failed_on .= $lang["template.cr.error.3"];
else if(strlen($detail) > 800) $failed_on .= $lang["template.cr.error.4"];
else if(strlen($notecarddetail) < 5) $failed_on = $lang["template.cr.error.5"];
$status = false;
if($failed_on == "")
{
    $template = new template();
    $template->set_name($name);
    $template->set_detail($detail);
    $template->set_notecarddetail($notecarddetail);
    $create_status = $template->create_entry();
    if($create_status["status"] == true)
    {
        $status = true;
        $ajax_reply->set_swap_tag_string("message",$lang["template.cr.info.1"]);
        $ajax_reply->set_swap_tag_string("redirect","template");
    }
    else
    {
        $ajax_reply->set_swap_tag_string("message",sprintf($lang["template.cr.error.6"],$create_status["message"]));
    }
}
else
{
    $ajax_reply->set_swap_tag_string("message",$failed_on);
}
?>
