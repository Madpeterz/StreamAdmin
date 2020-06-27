<?php
$treevender = new treevender();
$input = new inputFilter();
$name = $input->postFilter("name");
$failed_on = "";
$redirect = "";
if(strlen($name) < 5) $failed_on .= $lang["tree.cr.error.1"];
else if(strlen($name) > 100) $failed_on .= $lang["tree.cr.error.2"];
else if($treevender->load_by_field("name",$name) == true) $failed_on .= $lang["tree.cr.error.3"];
$status = false;
if($failed_on == "")
{
    $treevender = new treevender();
    $treevender->set_field("name",$name);
    $create_status = $treevender->create_entry();
    if($create_status["status"] == true)
    {
        $status = true;
        $redirect = "tree";
        echo $lang["tree.cr.info.1"];
    }
    else
    {
        echo sprintf($lang["tree.cr.error.4"],$create_status["message"]);
    }
}
else
{
    echo $failed_on;
}
?>
