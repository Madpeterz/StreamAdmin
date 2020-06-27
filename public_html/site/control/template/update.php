<?php
$input = new inputFilter();
$name = $input->postFilter("name");
$detail = $input->postFilter("detail");
$notecarddetail = $input->postFilter("notecarddetail");
$failed_on = "";
if(strlen($name) < 5) $failed_on .= $lang["template.up.error.1"];
else if(strlen($name) > 30) $failed_on .= $lang["template.up.error.2"];
else if(strlen($detail) < 5) $failed_on .= $lang["template.up.error.3"];
else if(strlen($detail) > 800) $failed_on .= $lang["template.up.error.4"];
else if(strlen($notecarddetail) < 5) $failed_on = $lang["template.up.error.5"];
$status = false;
if($failed_on == "")
{
    $template = new template();
    if($template->load($page) == true)
    {
        $template->set_field("name",$name);
        $template->set_field("detail",$detail);
        $template->set_field("notecarddetail",$notecarddetail);
        $update_status = $template->save_changes();
        if($update_status["status"] == true)
        {
            $status = true;
            echo $lang["template.up.info.1"];
            $redirect = "template";
        }
        else
        {
            echo sprintf($lang["template.up.error.7"],$update_status["message"]);
        }
    }
    else
    {
        echo $lang["template.up.error.6"];
    }
}
else
{
    echo $failed_on;
}
?>