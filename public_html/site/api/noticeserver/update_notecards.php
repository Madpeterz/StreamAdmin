<?php
$where_config = array(
    "fields" => array("id"),
    "values" => array(1),
    "types" => array("i"),
    "matches" => array("!=")
);
$status = true;
$notice_notecard_set = new notice_notecard_set();
$notice_notecard_set->load_with_config($where_config);
$notecards = $input->postFilter("notecards");
$notecards_list = array();
if($notecards != "none")
{
    if(strlen($notecards) > 0)
    {
        $notecards_list = explode(",",$notecards);
        // mark alive notecards / missing
        foreach($notice_notecard_set->get_all_ids() as $notice_notecard_id)
        {
            $notice_notecard = $notice_notecard_set->get_object_by_id($notice_notecard_id);
            $notecards_list_index = array_search($notice_notecard->get_name(),$notecards_list);
            if($notecards_list_index !== false)
            {
                unset($notecards_list[$notecards_list_index]);
            }
            if($notice_notecard->get_missing() == $notecards_list_index)
            {
                $notice_notecard->set_missing($notecards_list_index);
                $status = $notice_notecard->save_changes()["status"];
                if($status == false)
                {
                    print $lang["noticeserver.up.error.1"];
                    break;
                }
            }
        }
        // new notecards
        if($status == true)
        {
            foreach($notecards_list as $notecardname)
            {
                $notice_notecard = new notice_notecard();
                $notice_notecard->set_name($notecardname);
                $notice_notecard->set_missing(false);
                $status = $notice_notecard->create_entry();
                if($status == true)
                {
                    $notice_notecard_set->add_to_collected($notice_notecard);
                }
                else
                {
                    print $lang["noticeserver.up.error.2"];
                    break;
                }
            }
        }
    }
    else
    {
        $status = false;
        print $lang["noticeserver.up.error.3"];
    }
}
else
{
    if($notice_notecard_set->get_count() > 0)
    {
        $status = $notice_notecard_set->update_single_field_for_collection("missing",1)["status"];
        if($status == false)
        {
            print $lang["noticeserver.up.error.5"];
        }
    }
}
if($status == true)
{
    // remove dead notecards from db
    $notice_set = new notice_set();
    $notice_set->loadAll();
    if($notice_set->get_count() > 0)
    {
        $used_notecards = $notice_set->get_unique_array("notice_notecardlink");
        foreach($notice_notecard_set->get_all_ids() as $notice_notecard_id)
        {
            if(in_array($notice_notecard_id,$used_notecards) == false)
            {
                $notice_notecard = $notice_notecard_set->get_object_by_id($notice_notecard_id);
                if($notice_notecard->get_missing() == true)
                {
                    $status = $notice_notecard->remove_me()["status"];
                    if($status == false)
                    {
                        print $lang["noticeserver.up.error.4"];
                    }
                }
            }
        }

    }
}
if($status == true)
{
    print "ok";
}
?>
