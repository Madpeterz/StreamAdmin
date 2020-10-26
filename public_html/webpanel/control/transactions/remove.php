<?php
if($session->get_ownerlevel() == 1)
{
    $input = new inputFilter();
    $accept = $input->postFilter("accept");
    $ajax_reply->set_swap_tag_string("redirect","transactions");
    $status = false;
    if($accept == "Accept")
    {
        $transaction = new transactions();
        if($transaction->load_by_field("transaction_uid",$page) == true)
        {
            $remove_status = $transaction->remove_me();
            if($remove_status["status"] == true)
            {
                $status = true;
                $ajax_reply->set_swap_tag_string("message",$lang["tr.rm.info.1"]);
            }
            else
            {
                $ajax_reply->set_swap_tag_string("message",sprintf($lang["tr.rm.error.3"],$remove_status["message"]));
            }
        }
        else
        {
            $ajax_reply->set_swap_tag_string("message",$lang["tr.rm.error.2"]);
        }
    }
    else
    {
        $ajax_reply->set_swap_tag_string("message",$lang["tr.rm.error.1"]);
    }
}
else
{
    $ajax_reply->set_swap_tag_string("message",$lang["tr.rm.error.4"]);
}
?>
