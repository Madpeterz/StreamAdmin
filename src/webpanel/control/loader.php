<?php
include "shared/lang/control/".$site_lang.".php";
$status = true;
$reply = [];
$soft_fail = false;
if($session->get_logged_in() == true)
{
    if(file_exists("webpanel/control/".$module."/".$area.".php") == true)
    {
        if(file_exists("shared/lang/control/".$module."/".$site_lang.".php") == true)
        {
            include "shared/lang/control/".$module."/".$site_lang.".php";
        }
        include "webpanel/control/".$module."/".$area.".php";
    }
    else
    {
        $status = false;
        $ajax_reply->set_swap_tag_string("message",$lang["ld.error.1"]);
    }
}
else
{
    if(file_exists("webpanel/control/login/".$area.".php") == true)
    {
        include "shared/lang/control/login/".$site_lang.".php";
        include "webpanel/control/login/".$area.".php";
    }
    else
    {
        $status = false;
        $ajax_reply->set_swap_tag_string("message",$lang["ld.error.2"]);
    }
}
if($status == false)
{
    if($soft_fail == false)
    {
        $sql->flagError();
    }
}
$reply["status"] = $status;
$reply["message"] = $ajax_reply->get_swap_tag_string("message");
if($ajax_reply->get_swap_tag_string("redirect") != null)
{
    $redirect_target = $ajax_reply->get_swap_tag_string("redirect");
    if($redirect_target == "here")
    {
        $ajax_reply->set_swap_tag_string("redirect","");
    }
    $reply["redirect"] = "".$ajax_reply->url_base()."".$ajax_reply->get_swap_tag_string("redirect")."";
}
$ajax_reply->set_swap_tag_string("content",trim(json_encode($reply)));
$ajax_reply->render_page();
?>
