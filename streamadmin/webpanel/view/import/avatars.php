<?php
$current_sql = $sql;
$old_sql = new mysqli_controler();
$old_sql->sqlStart_test($r4_db_username,$r4_db_pass,$r4_db_name,false,$r4_db_host);

$sql = $old_sql; // switch to r4

$r4_users_set = new r4_users_set();
$r4_users_set->loadAll();

$sql = $current_sql; // swtich back to r7

include("shared/lang/control/avatar/".$site_lang.".php");

$all_ok = true;
$avatars_created = 0;

$seen_avatar_uuids = array();
foreach($r4_users_set->get_all_ids() as $r4_user_id)
{
    $r4_user = $r4_users_set->get_object_by_id($r4_user_id);
    if(in_array($r4_user->get_slkey(),$seen_avatar_uuids) == false)
    {
        $seen_avatar_uuids[] = $r4_user->get_slkey();
        $avatar_helper = new avatar_helper();
        $status = $avatar_helper->load_or_create($r4_user->get_slkey(),$r4_user->get_slname());
        if($status == true)
        {
            $avatars_created++;
        }
        else
        {
            $all_ok = false;
            break;
        }
    }
}

if($all_ok == true)
{
    $view_reply->add_swap_tag_string("page_content","Created: ".$avatars_created." avatars <br/> <a href=\"[[url_base]]import\">Back to menu</a>");
}
else
{
    $view_reply->add_swap_tag_string("page_content",$lang["av.cr.error.5"]);
    $sql->flagError();
}
?>
