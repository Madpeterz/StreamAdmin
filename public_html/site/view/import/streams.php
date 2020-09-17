<?php
$current_sql = $sql;
$old_sql = new mysqli_controler();
$old_sql->sqlStart_test($r4_db_username,$r4_db_pass,$r4_db_name,false,$r4_db_host);

$sql = $old_sql; // switch to r4

$r4_items_set = new r4_items_set();
$r4_items_set->loadAll();

$r4_packages_set = new r4_packages_set();
$r4_packages_set->loadAll();

$r4_package_id_to_name = $r4_packages_set->get_linked_array("id","name");

include("site/lang/control/stream/".$site_lang.".php");

$sql = $current_sql; // swtich back to r7

$servers_set = new server_set();
$servers_set->loadAll();

$package_set = new package_set();
$package_set->loadAll();

$package_name_to_id = $package_set->get_linked_array("name","id");
$server_domain_to_id = $servers_set->get_linked_array("domain","id");

$stream_created = 0;
$stream_skipped_no_package = 0;
$stream_skipped_no_server = 0;
$all_ok = true;

foreach($r4_items_set->get_all_ids() as $r4_item_id)
{
    $r4_item = $r4_items_set->get_object_by_id($r4_item_id);
    if(array_key_exists($r4_item->get_packageid(),$r4_package_id_to_name) == true)
    {
        $find_package = "R4|".$r4_item->get_packageid()."|".$r4_package_id_to_name[$r4_item->get_packageid()]."";
        if(array_key_exists($find_package,$package_name_to_id) == true)
        {

            if(array_key_exists($r4_item->get_streamurl(),$server_domain_to_id) == true)
            {
                $stream = new stream();
                $uid = $stream->create_uid("stream_uid",8,10);
                if($uid["status"] == true)
                {
                    $stream->set_stream_uid($uid["uid"]);
                    $stream->set_packagelink($package_name_to_id[$find_package]);
                    $stream->set_serverlink($server_domain_to_id[$r4_item->get_streamurl()]);
                    $stream->set_port($r4_item->get_streamport());
                    $stream->set_needwork($r4_item->get_baditem());
                    $stream->set_adminpassword($r4_item->get_adminpassword());
                    $stream->set_adminusername($r4_item->get_adminusername());
                    $stream->set_original_adminusername($r4_item->get_adminusername());
                    $stream->set_djpassword($r4_item->get_streampassword());
                    $stream->set_mountpoint("r4|".$r4_item->get_id()."");
                    $create_status = $stream->create_entry();
                    if($create_status["status"] == true)
                    {
                        $stream_created++;
                    }
                    else
                    {
                        echo sprintf($lang["stream.cr.error.14"],$create_status["message"]);
                        $all_ok = false;
                        break;
                    }
                }
                else
                {
                    echo $lang["stream.cr.error.11"];
                    $all_ok = false;
                    break;
                }
            }
            else
            {
                $stream_skipped_no_server++;
            }
        }
        else
        {
            $stream_skipped_no_package++;
        }
    }
    else
    {
        $stream_skipped_no_package++;
    }
}
if($all_ok == true)
{
    echo "Created: ".$stream_created." streams, ".$stream_skipped_no_server." skipped (No server), ".$stream_skipped_no_package." skipped (No package) <br/> <a href=\"[[url_base]]import\">Back to menu</a>";
}
else
{
    $sql->flagError();
}

?>
