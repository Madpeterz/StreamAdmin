<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect = "template";
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
            print $lang["template.rm.info.1"];
        }
        else
        {
            print sprintf($lang["template.cr.error.6"],$remove_status["message"]);
        }
    }
    else
    {
        print $lang["tempalte.rm.error.2"];
    }
}
else
{
    print $lang["tempalte.rm.error.1"];
    $redirect ="template/manage/".$page."";
}
?>
