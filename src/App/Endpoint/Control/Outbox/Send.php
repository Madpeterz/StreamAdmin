<?php

namespace App\Endpoint\Control\Outbox;

use App\Models\Avatar;
use App\Models\AvatarSet;
use App\Models\BanlistSet;
use App\Models\Botconfig;
use App\Models\NoticeSet;
use App\Models\PackageSet;
use App\Models\RentalSet;
use App\Models\ServerSet;
use App\Models\StreamSet;
use App\Template\ViewAjax;
use bot_helper;
use swapables_helper;
use YAPF\InputFilter\InputFilter;

class Send extends ViewAjax
{
    public function process(): void
    {
        $input_filter = new InputFilter();
        $rental_set = new RentalSet();
        $stream_set = new StreamSet();
        $avatar_set = new AvatarSet();
        $banlist_set = new BanlistSet();
        $bot_helper = new bot_helper();
        $swapables_helper = new swapables_helper();
        $botconfig = new Botconfig();
        $botavatar = new Avatar();
        $notice_set = new NoticeSet();
        $server_set = new ServerSet();
        $package_set = new PackageSet();

        $message = $input_filter->postFilter("message");
        $max_avatars = $input_filter->postFilter("max_avatars", "integer");
        $source = $input_filter->postFilter("source");
        $source_id = $input_filter->postFilter("source_id", "integer");
        $avatarids = $input_filter->postFilter("avatarids", "array");
        if (count($avatarids) > $max_avatars) {
            $this->setSwapTag("message", "To many avatars sent vs what was expected");
            return;
        }
        if ($source == "notice") {
            $rental_set->loadOnField("noticeLink", $source_id);
        } elseif ($source == "server") {
            $stream_set->loadOnField("serverLink", $source_id);
            $rental_set->loadIds($stream_set->getAllIds(), "streamLink");
        } elseif ($source == "package") {
            $rental_set->loadOnField("packageLink", $source_id);
        }
        if ($rental_set->getCount() == 0) {
            $this->setSwapTag("message", "No rentals found with selected source/id pair");
            return;
        }
        $stream_set = new StreamSet();
        $stream_set->loadIds($rental_set->getAllByField("streamLink"));
        $avatar_set->loadIds($rental_set->getUniqueArray("avatarLink"));
        $banlist_set->loadIds($rental_set->getUniqueArray("avatarLink"), "avatarLink");
        $banned_ids = $banlist_set->getAllByField("avatarLink");
        $max_avatar_count = $avatar_set->getCount() - $banlist_set->getCount();
        if ($max_avatar_count == 0) {
            $this->setSwapTag("message", "No avatars found to send to");
            return;
        }
        $package_set->loadAll();
        $server_set->loadAll();
        $notice_set->loadAll();
        $botconfig->loadID(1);
        $botavatar->loadID($botconfig->getAvatarLink());

        $sent_counter = 0;
        $seen_avatars = [];
        foreach ($rental_set->getAllIds() as $rental_id) {
            $rental = $rental_set->getObjectByID($rental_id);
            if (in_array($rental->getAvatarLink(), $avatarids) == true) {
                if (in_array($rental->getAvatarLink(), $seen_avatars) == false) {
                        $seen_avatars[] = $rental->getAvatarLink();
                        $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
                    if (in_array($avatar->getId(), $banned_ids) == false) {
                        $stream = $stream_set->getObjectByID($rental->getStreamLink());
                        $package = $package_set->getObjectByID($stream->getPackageLink());
                        $server = $server_set->getObjectByID($stream->getServerLink());
                        $sendmessage = $swapables_helper->get_swapped_text(
                            $message,
                            $avatar,
                            $rental,
                            $package,
                            $server,
                            $stream
                        );
                        $bot_helper->send_message($botconfig, $botavatar, $avatar, $sendmessage, true);
                        $sent_counter++;
                    }
                }
            }
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", sprintf("Sent to %1\$s avatars", $sent_counter));
        $this->setSwapTag("redirect", "outbox");
    }
}
