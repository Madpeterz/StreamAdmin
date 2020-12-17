<?php

$input_filter = new inputFilter();
$message = $input_filter->postFilter("message");
$max_avatars = $input_filter->postFilter("max_avatars", "integer");
$source = $input_filter->postFilter("source");
$source_id = $input_filter->postFilter("source_id", "integer");
$avatarids = $input_filter->postFilter("avatarids", "array");
if (count($avatarids) <= $max_avatars) {
    $rental_set = new rental_set();
    $ok = false;
    if ($source == "notice") {
        $rental_set->loadOnField("noticelink", $source_id);
        $ok = true;
    } elseif ($source == "server") {
        $stream_set = new stream_set();
        $stream_set->loadOnField("serverlink", $source_id);
        $rental_set->loadIds($stream_set->getAllIds(), "streamlink");
        $ok = true;
    } elseif ($source == "package") {
        $rental_set->loadOnField("packagelink", $source_id);
        $ok = true;
    }
    $status = false;
    if ($ok == true) {
        if ($rental_set->getCount() > 0) {
            $stream_set = new stream_set();
            $stream_set->loadIds($rental_set->getAllByField("streamlink"));
            $avatar_set = new avatar_set();
            $avatar_set->loadIds($rental_set->getUniqueArray("avatarlink"));
            $banlist_set = new banlist_set();
            $banlist_set->loadIds($rental_set->getUniqueArray("avatarlink"), "avatar_link");
            $banned_ids = $banlist_set->getAllByField("avatarlink");
            $max_avatar_count = $avatar_set->getCount() - $banlist_set->getCount();
            if ($max_avatar_count > 0) {
                $package_set = new package_set();
                $package_set->loadAll();
                $server_set = new server_set();
                $server_set->loadAll();
                $notice_set = new notice_set();
                $notice_set->loadAll();

                $bot_helper = new bot_helper();
                $swapables_helper = new swapables_helper();

                $botconfig = new botconfig();
                $botconfig->loadID(1);

                $botavatar = new avatar();
                $botavatar->loadID($botconfig->getAvatarlink());

                $sent_counter = 0;
                $seen_avatars = [];
                foreach ($rental_set->getAllIds() as $rental_id) {
                    $rental = $rental_set->getObjectByID($rental_id);
                    if (in_array($rental->getAvatarlink(), $avatarids) == true) {
                        if (in_array($rental->getAvatarlink(), $seen_avatars) == false) {
                            $seen_avatars[] = $rental->getAvatarlink();
                            $avatar = $avatar_set->getObjectByID($rental->getAvatarlink());
                            if (in_array($avatar->getId(), $banned_ids) == false) {
                                $stream = $stream_set->getObjectByID($rental->getStreamlink());
                                $package = $package_set->getObjectByID($stream->getPackagelink());
                                $server = $server_set->getObjectByID($stream->getServerlink());

                                $sendmessage = $swapables_helper->get_swapped_text($message, $avatar, $rental, $package, $server, $stream);
                                $send_message_status = $bot_helper->send_message($botconfig, $botavatar, $avatar, $sendmessage, true);
                                $sent_counter++;
                            }
                        }
                    }
                }
                $status = true;
                $this->output->setSwapTagString("message", sprintf($lang["outbox.send.ok"], $sent_counter));
                $this->output->setSwapTagString("redirect", "outbox");
            } else {
                $this->output->setSwapTagString("message", $lang["outbox.send.error.4"]);
            }
        } else {
            $this->output->setSwapTagString("message", $lang["outbox.send.error.3"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["outbox.send.error.2"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["outbox.send.error.1"]);
}
