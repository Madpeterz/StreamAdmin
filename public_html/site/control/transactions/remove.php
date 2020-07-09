<?php
if($session->get_ownerlevel() == 1)
{
    $input = new inputFilter();
    $accept = $input->postFilter("accept");
    $redirect = "transactions";
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
                echo $lang["tr.rm.info.1"];
            }
            else
            {
                echo sprintf($lang["tr.rm.error.3"],$remove_status["message"]);
            }
        }
        else
        {
            echo $lang["tr.rm.error.2"];
        }
    }
    else
    {
        echo $lang["tr.rm.error.1"];
    }
}
else
{
    echo $lang["tr.rm.error.4"];
}
?>
