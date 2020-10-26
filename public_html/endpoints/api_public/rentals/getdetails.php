<?php
$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$rental = new rental();
$localstatus = false;
if($rental->load_by_field("rental_uid",$rental_uid) == true)
{
    if($rental->get_avatarlink() == $object_owner_avatar->get_id())
    {
        $localstatus = true;
        $_POST["rental_uid"] = $rental_uid;
        $lang_file = "shared/lang/api/details/".$site_lang.".php";
        if(file_exists($lang_file) == true)
        {
            include($lang_file);
        }
        include("endpoints/api/details/resend.php");
    }
}
if($localstatus == false)
{
    $status = false;
    echo "unable to find rental";
}
?>
