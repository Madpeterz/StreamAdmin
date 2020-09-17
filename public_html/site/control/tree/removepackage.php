<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect ="tree";
$status = false;
if($accept == "Accept")
{
    $treevender_packages = new treevender_packages();
    if($treevender_packages->load($page) == true)
    {
        $redirect_to = $treevender_packages->get_treevenderlink();
        $remove_status = $treevender_packages->remove_me();
        if($remove_status["status"] == true)
        {
            $status = true;
            $redirect = "tree/manage/".$redirect_to."";
            print $lang["tree.rp.info.1"];
        }
        else
        {
            print sprintf($lang["tree.rp.error.3"],$remove_status["message"]);
        }
    }
    else
    {
        print $lang["tree.rp.error.1"];
    }
}
else
{
    print $lang["tree.rp.error.1"];
}
?>
