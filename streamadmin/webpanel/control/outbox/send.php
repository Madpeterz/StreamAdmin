<?php
$input_filter = new inputFilter();
$message = $input_filter->postFilter("message");
$max_avatars = $input_filter->postFilter("max_avatars","integer");
$source = $input_filter->postFilter("source");
$source_id = $input_filter->postFilter("source_id","integer");
$avatarids = $input_filter->postFilter("avatarids","array");
if(count($avatarids) <= $max_avatars)
{
    $rental_set = new rental_set();
    $ok = false;
    if($source == "notice")
    {
        $rental_set->load_on_field("noticelink",$source_id);
        $ok = true;
    }
    else if($source == "server")
    {
        $stream_set = new stream_set();
        $stream_set->load_on_field("serverlink",$source_id);
        $rental_set->load_ids($stream_set->get_all_ids(),"streamlink");
        $ok = true;
    }
    else if($source == "package")
    {
        $rental_set->load_on_field("packagelink",$source_id);
        $ok = true;
    }
    $status = false;
    if($ok == true)
    {
        if($rental_set->get_count() > 0)
        {
            $stream_set = new stream_set();
            $stream_set->load_ids($rental_set->get_all_by_field("streamlink"));
            $avatar_set = new avatar_set();
            $avatar_set->load_ids($rental_set->get_unique_array("avatarlink"));
            $banlist_set = new banlist_set();
            $banlist_set->load_ids($rental_set->get_unique_array("avatarlink"),"avatar_link");
            $banned_ids = $banlist_set->get_all_by_field("avatarlink");
            $max_avatar_count = $avatar_set->get_count()-$banlist_set->get_count();
            if($max_avatar_count > 0)
            {
                $package_set = new package_set();
                $package_set->loadAll();
                $server_set = new server_set();
                $server_set->loadAll();
                $notice_set = new notice_set();
                $notice_set->loadAll();

                $bot_helper = new bot_helper();
                $swapables_helper = new swapables_helper();

                $botconfig = new botconfig();
                $botconfig->load(1);

                $botavatar = new avatar();
                $botavatar->load($botconfig->get_avatarlink());

                $sent_counter = 0;
                $seen_avatars = array();
                foreach($rental_set->get_all_ids() as $rental_id)
                {
                    $rental = $rental_set->get_object_by_id($rental_id);
                    if(in_array($rental->get_avatarlink(),$avatarids) == true)
                    {
                        if(in_array($rental->get_avatarlink(),$seen_avatars) == false)
                        {
                            $seen_avatars[] = $rental->get_avatarlink();
                            $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
                            if(in_array($avatar->get_id(),$banned_ids) == false)
                            {
                                $stream = $stream_set->get_object_by_id($rental->get_streamlink());
                                $package = $package_set->get_object_by_id($stream->get_packagelink());
                                $server = $server_set->get_object_by_id($stream->get_serverlink());

                                $sendmessage = $swapables_helper->get_swapped_text($message,$avatar,$rental,$package,$server,$stream);
                                $send_message_status = $bot_helper->send_message($botconfig,$botavatar,$avatar,$sendmessage,true);
                                $sent_counter++;
                            }
                        }
                    }
                }
                $status = true;
                $ajax_reply->set_swap_tag_string("message",sprintf($lang["outbox.send.ok"],$sent_counter));
                $ajax_reply->set_swap_tag_string("redirect","outbox");
            }
            else
            {
                $ajax_reply->set_swap_tag_string("message",$lang["outbox.send.error.4"]);
            }
        }
        else
        {
            $ajax_reply->set_swap_tag_string("message",$lang["outbox.send.error.3"]);
        }
    }
    else
    {
        $ajax_reply->set_swap_tag_string("message",$lang["outbox.send.error.2"]);
    }
}
else
{
    $ajax_reply->set_swap_tag_string("message",$lang["outbox.send.error.1"]);
}
?>
