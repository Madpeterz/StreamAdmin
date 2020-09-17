<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$status = false;
$redirect ="package";
if($accept == "Accept")
{
    $package = new package();
    if($package->load_by_field("package_uid",$page) == true)
    {
        $stream_set = new stream_set();
        $load_status = $stream_set->load_on_field("packagelink",$package->get_id());
        if($load_status["status"] == true)
        {
            if($stream_set->get_count() == 0)
            {
                $transaction_set = new transactions_set();
                $load_status = $transaction_set->load_on_field("packagelink",$package->get_id());
                if($load_status["status"] == true)
                {
                    if($transaction_set->get_count() == 0)
                    {
                        $rental_set = new rental_set();
                        $load_status = $rental_set->load_on_field("packagelink",$package->get_id());
                        if($load_status["status"] == true)
                        {
                            if($rental_set->get_count() == 0)
                            {
                                $treevender_packages_set = new treevender_packages_set();
                                $load_status = $treevender_packages_set->load_on_field("packagelink",$package->get_id());
                                if($load_status["status"] == true)
                                {
                                    if($treevender_packages_set->get_count() == 0)
                                    {
                                        $remove_status = $package->remove_me();
                                        if($remove_status["status"] == true)
                                        {
                                            $status = true;
                                            echo $lang["package.rm.info.1"];
                                        }
                                        else
                                        {
                                            echo sprintf($lang["package.rm.error.3"],$remove_status["message"]);
                                        }
                                    }
                                    else
                                    {
                                        echo sprintf($lang["package.rm.error.11"],$treevender_packages_set->get_count());
                                    }
                                }
                                else
                                {
                                    echo $lang["package.rm.error.10"];
                                }
                            }
                            else
                            {
                                echo sprintf($lang["package.rm.error.9"],$rental_set->get_count());
                            }
                        }
                        else
                        {
                            echo $lang["package.rm.error.8"];
                        }
                    }
                    else
                    {
                        echo sprintf($lang["package.rm.error.7"],$stream_set->get_count());
                    }
                }
                else
                {
                    echo $lang["package.rm.error.6"];
                }
            }
            else
            {
                echo sprintf($lang["package.rm.error.5"],$stream_set->get_count());
            }
        }
        else
        {
            echo $lang["package.rm.error.4"];
        }
    }
    else
    {
        echo $lang["package.rm.error.2"];
    }
}
else
{
    echo $lang["package.rm.error.1"];
    $redirect ="package/manage/".$page."";
}
?>
