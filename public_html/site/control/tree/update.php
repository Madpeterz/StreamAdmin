<?php
$input = new inputFilter();
$name = $input->postFilter("name");
$failed_on = "";
$redirect = "";
if(strlen($name) < 5) $failed_on .= $lang["tree.up.error.1"];
else if(strlen($name) > 100) $failed_on .= $lang["tree.up.error.2"];
$status = false;
if($failed_on == "")
{
    $treevender = new treevender();
    if($treevender->load($page) == true)
    {
        $where_fields = array(array("name"=>"="));
        $where_values = array(array($name =>"s"));
        $count_check = $sql->basic_count($treevender->get_table(),$where_fields,$where_values);
        $expected_count = 0;
        if($treevender->get_name() == $name)
        {
            $expected_count = 1;
        }
        if($count_check["status"] == true)
        {
            if($count_check["count"] == $expected_count)
            {
                $treevender->set_name($name);
                $update_status = $treevender->save_changes();
                if($update_status["status"] == true)
                {
                    $status = true;
                    $redirect = "tree";
                    print $lang["tree.up.info.1"];
                }
                else
                {
                    print sprintf($lang["tree.up.error.6"],$update_status["message"]);
                }
            }
            else
            {
                print $lang["tree.up.error.5"];
            }
        }
        else
        {
            print $lang["tree.up.error.4"];
        }
    }
    else
    {
        print $lang["tree.up.error.3"];
        $redirect = "tree";
    }
}
else
{

    print $failed_on;
}
?>
