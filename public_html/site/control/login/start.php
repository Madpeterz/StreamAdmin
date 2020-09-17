<?php
$input = new inputFilter();
$staffusername = $input->postFilter("staffusername");
$staffpassword = $input->postFilter("staffpassword");
$status = false;
if((strlen($staffusername) > 0) && (strlen($staffpassword) > 0))
{
    if($session->login_with_username_password($staffusername,$staffpassword) == true)
    {
        $status = true;
        print $lang["login.st.info.1"];
        $redirect = "here";
    }
    else
    {
        print $lang["login.st.error.1"];
    }
}
else
{
    print $lang["login.st.error.1"];
}
?>
