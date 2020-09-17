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
        echo $lang["login.st.info.1"];
        $redirect = "here";
    }
    else
    {
        echo $lang["login.st.error.1"];
    }
}
else
{
    echo $lang["login.st.error.1"];
}
?>
