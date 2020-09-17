<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect ="notice";
$status = false;
if($accept == "Accept")
{
    if(in_array($page,array(6,10)) == false)
    {
        $notice = new notice();
        if($notice->load($page) == true)
        {
            $notecard_set = new notecard_set();
            $load_status = $notecard_set->load_on_field("noticelink",$notice->get_id());
            if($load_status["status"] == true)
            {
                if($notecard_set->get_count() == 0)
                {
                    $remove_status = $notice->remove_me();
                    if($remove_status["status"] == true)
                    {
                        $status = true;
                        echo $lang["notice.rm.info.1"];
                    }
                    else
                    {
                        echo sprintf($lang["notice.rm.error.4"],$remove_status["message"]);
                    }
                }
                else
                {
                    echo sprintf($lang["notice.rm.error.6"],$notecard_set->get_count());
                }
            }
            else
            {
                echo $lang["notice.rm.error.5"];
            }
        }
        else
        {
            echo $lang["notice.rm.error.3"];
        }
    }
    else
    {
        echo $lang["notice.rm.error.2"];
    }
}
else
{
    echo $lang["notice.rm.error.1"];
    $redirect ="notice/manage/".$page."";
}
?>
