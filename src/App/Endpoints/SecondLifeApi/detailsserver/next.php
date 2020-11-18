<?php

$status = false;
if ($owner_override == true) {
    $botconfig = new botconfig();
    if ($botconfig->load(1) == 1) {
        $botavatar = new avatar();
        if ($botavatar->load($botconfig->getAvatarlink()) == true) {
            $detail_set = new detail_set();
            $detail_set->load_newest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
            if ($detail_set->getCount() > 0) {
                $detail = $detail_set->get_first();
                $rental = new rental();
                if ($rental->load($detail->getRentallink()) == true) {
                    $avatar = new avatar();
                    if ($avatar->load($rental->getAvatarlink()) == true) {
                        $stream = new stream();
                        if ($stream->load($rental->get_streamlink()) == true) {
                            $server = new server();
                            if ($server->load($stream->get_serverlink()) == true) {
                                $package = new package();
                                if ($package->load($stream->get_packagelink()) == true) {
                                    $template = new template();
                                    if ($template->load($package->get_templatelink()) == true) {
                                        $remove_status = $detail->remove_me();
                                        if ($remove_status["status"] == true) {
                                            $bot_helper = new bot_helper();
                                            $swapables_helper = new swapables_helper();
                                            $sendmessage = $swapables_helper->get_swapped_text($template->get_detail(), $avatar, $rental, $package, $server, $stream);
                                            $send_message_status = $bot_helper->send_message($botconfig, $botavatar, $avatar, $sendmessage, true);
                                            if ($send_message_status["status"] == true) {
                                                if ($botconfig->get_notecards() == true) {
                                                     $notecard = new notecard();
                                                     $notecard->set_rentallink($rental->getId());
                                                     $create_status = $notecard->create_entry();
                                                }
                                                $status = true;
                                                echo "ok";
                                            } else {
                                                echo $lang["detailsserver.n.error.11"];
                                            }
                                        } else {
                                            echo $lang["detailsserver.n.error.10"];
                                        }
                                    } else {
                                        echo $lang["detailsserver.n.error.9"];
                                    }
                                } else {
                                    echo $lang["detailsserver.n.error.8"];
                                }
                            } else {
                                echo $lang["detailsserver.n.error.7"];
                            }
                        } else {
                            echo $lang["detailsserver.n.error.6"];
                        }
                    } else {
                        echo $lang["detailsserver.n.error.5"];
                    }
                } else {
                    echo $lang["detailsserver.n.error.4"];
                }
            } else {
                $status = true;
                echo "nowork";
            }
        } else {
            echo $lang["detailsserver.n.error.3"];
        }
    } else {
        echo $lang["detailsserver.n.error.2"];
    }
} else {
    echo $lang["detailsserver.n.error.1"];
}
