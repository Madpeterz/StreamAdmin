<?php
$status = false;
$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$amount = $input->postFilter("amount","integer");
$transactionid = $input->postFilter("transactionid","uuid");
$tidhash = $input->postFilter("tidhash");
$tidtime = $input->postFilter("tidtime","integer");
$regionname = $input->postFilter("regionname");
$fasttest = array($amount,$rental_uid,$transactionid,$tidhash,$tidtime,$regionname);
$localstatus = false;
$why_failed = "topup has failed";
if(in_array(null,$fasttest) == false)
{
    $region_helper = new region_helper();
    $get_region_status = $region_helper->load_or_create($regionname);
    if($get_region_status == true)
    {
        $region = $region_helper->get_region();
        $rental = new rental();
        $status = false;
        if($rental->load_by_field("rental_uid",$rental_uid) == true)
        {
            if($rental->get_avatarlink() == $object_owner_avatar->get_id())
            {
                $package = new package();
                if($package->load($rental->get_packagelink()) == true)
                {
                    if($amount == $package->get_cost())
                    {
                        $bits = array($rental_uid,$amount,$transactionid,$tidtime,$object_owner_avatar->get_avataruuid(),$slconfig->get_publiclinkcode(),$rental->get_expireunixtime());
                        $raw = implode("",$bits);
                        $tidhashcheck = sha1($raw);
                        if($tidhashcheck == $tidhash)
                        {
                            $avatar_system = new avatar();
                            if($avatar_system->load($slconfig->get_owner_av()) == true)
                            {
                                $reseller = $avatar_system;
                                $localstatus = true;
                                $_POST["rental_uid"] = $rental_uid;
                                $_POST["avataruuid"] = $object_owner_avatar->get_avataruuid();
                                $_POST["avatarname"] = $object_owner_avatar->get_avatarname();
                                $_POST["amountpaid"] = $amount;
                                $owner_override = true;

                                $lang_file = "shared/lang/api/renew/".$site_lang.".php";
                                if(file_exists($lang_file) == true)
                                {
                                    include $lang_file;
                                }
                                include "endpoints/api/renew/renewnow.php";
                                if($status == true)
                                {
                                    $bot_helper = new bot_helper();
                                    $swapables_helper = new swapables_helper();

                                    $botconfig = new botconfig();
                                    $botconfig->load(1);

                                    $botavatar = new avatar();
                                    $botavatar->load($botconfig->get_avatarlink());

                                    $sendmessage = $swapables_helper->get_swapped_text("= Remote transaction notice =[[NL]] User: [[AVATAR_FULLNAME]] has topped up L$".$amount." [[NL]] Rental: ".$rental->get_rental_uid()." on port: ".$stream->get_port()." [[NL]] transaction ID:".$transactionid."",$object_owner_avatar,$rental,$package,$server,$stream);
                                    $send_message_status = $bot_helper->send_message($botconfig,$botavatar,$avatar_system,$sendmessage,true);
                                }
                            }
                            else { $why_failed = "cant load system owner"; }
                        }
                        else { $why_failed = "tid hash error"; }
                    }
                    else { $why_failed = "incorrect amount"; }
                }
                else { $why_failed = "cant find package"; }
            }
            else { $why_failed = "ownership issue for topup"; }
        }
        else { $why_failed = "Cant find rental"; }
    }
    else { $why_failed = "Unknown region"; }
}
else { $why_failed = "One or more values are null"; }
if($localstatus == false)
{
    $status = false;
    echo $why_failed;
}
?>
