<?php
if($session->get_ownerlevel() == 1)
{
    $input = new inputFilter();
    $db_host = $input->postFilter("db_host");
    $db_name = $input->postFilter("db_name");
    $db_username = $input->postFilter("db_username");
    $db_pass = $input->postFilter("db_pass");

    $saveconfig = '<?php $r4_db_host="'.$db_host.'"; $r4_db_name="'.$db_name.'"; $r4_db_username="'.$db_username.'"; $r4_db_pass="'.$db_pass.'";?>';
    if(file_exists("site/config/r4.php") == true)
    {
        unlink("site/config/r4.php");
    }
    file_put_contents("site/config/r4.php",$saveconfig);
    $status = true;
    print "ok";
    $redirect = "import";
}
else
{
    $status = false;
    print "Only the system owner can access this area";
}
?>
