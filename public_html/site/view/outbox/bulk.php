<?php
$rental_set = new rental_set();

$input_filter = new inputFilter();
$source_id = -1;
$souce_named = "";
$ok = false;
$message = $input_filter->postFilter("message");

if(strlen($message) >= 10)
{
    if(strlen($message) <= 800)
    {
        if($page == "notice")
        {
            $source_id = $input_filter->postFilter("noticelink","integer");
            if($source_id != null)
            {
                $notice = new notice();
                $notice->load($source_id);
                $souce_named = $notice->get_name();
                $rental_set->load_on_field("noticelink",$source_id);
                $ok = true;
            }
        }
        else if($page == "server")
        {
            $source_id = $input_filter->postFilter("serverlink","integer");
            if($source_id != null)
            {
                $server = new server();
                $server->load($source_id);
                $souce_named = $server->get_domain();
                $stream_set = new stream_set();
                $stream_set->load_on_field("serverlink",$source_id);
                $rental_set->load_ids($stream_set->get_all_ids(),"streamlink");
                $ok = true;
            }
        }
        else if($page == "package")
        {
            $source_id = $input_filter->postFilter("packagelink","integer");
            if($source_id != null)
            {
                $package = new package();
                $package->load($source_id);
                $souce_named = $package->get_name();
                $rental_set->load_on_field("packagelink",$source_id);
                $ok = true;
            }
        }
        if($ok == true)
        {
            $template_parts["page_title"] .= "Bulk sending to ".$page.": ".$souce_named."";
            $stream_set = new stream_set();
            $stream_set->load_ids($rental_set->get_all_by_field("streamlink"));
            $avatar_set = new avatar_set();
            $avatar_set->load_ids($rental_set->get_unique_array("avatarlink"));
            $banlist_set = new banlist_set();
            $banlist_set->load_ids($rental_set->get_unique_array("avatarlink"),"avatar_link");

            $max_avatar_count = $avatar_set->get_count()-$banlist_set->get_count();
            if($max_avatar_count > 0)
            {
                $form = new form();
                $form->target("outbox/send");
                $form->hidden_input("message",$message);
                $form->hidden_input("max_avatars",$max_avatar_count);
                $form->hidden_input("source",$page);
                $form->hidden_input("source_id",$source_id);

                $table_head = array("id","X","Name");
                $table_body = array();

                $banned_ids = $banlist_set->get_all_by_field("avatarlink");
                foreach($avatar_set->get_all_ids() as $avatar_id)
                {
                    if(in_array($avatar_id,$banned_ids) == false)
                    {
                        $avatar = $avatar_set->get_object_by_id($avatar_id);
                        $entry = array();
                        $entry[] = $avatar->get_id();
                        $entry[] = '<div class="checkbox"><input checked type="checkbox" id="avatarmail'.$avatar_id.'" name="avatarids[]" value="'.$avatar_id.'"></div>';
                        $entry[] = '<div class="checkbox"><label for="avatarmail'.$avatar_id.'">'.$avatar->get_avatarname().'</label></div>';
                        $table_body[] = $entry;
                    }
                }
                $form->col(12);
                    $form->direct_add(render_datatable($table_head,$table_body));
                echo $form->render("Send to selected","success");
                echo "<br/><hr/>Note: If an avatar has multiple streams that match the selected filter source the first rental will be used.";
            }
            else
            {
                redirect("outbox?message=No selectable avatars for the ".$page."");
            }
        }
        else
        {
            redirect("outbox?message=Filter option not supported");
        }
    }
    else
    {
        redirect("outbox?message=Message length to long");
    }
}
else
{
    redirect("outbox?message=Message length to short");
}
?>
