<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect ="tree";
$status = false;
if($accept == "Accept")
{
    $treevender = new treevender();
    if($treevender->load($page) == true)
    {
        $treevender_package_set = new treevender_packages_set();
        $treevender_package_set->load_on_field("treevenderlink",$treevender->get_id());
        $purge_status = $treevender_package_set->purge_collection_set();
        if($purge_status["status"] == true)
        {
            $remove_status = $treevender->remove_me();
            if($remove_status["status"] == true)
            {
                $status = true;
                echo $lang["tree.rm.info.1"];
            }
            else
            {
                echo sprintf($lang["tree.rm.error.4"],$remove_status["message"]);
            }
        }
        else
        {
            echo $lang["tree.rm.error.3"];
        }
    }
    else
    {
        echo $lang["tree.rm.error.2"];
    }
}
else
{
    echo $lang["tree.rm.error.1"];
    $redirect ="tree/manage/".$page."";
}
?>
