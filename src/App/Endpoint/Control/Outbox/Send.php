<?php

namespace App\Endpoint\Control\Outbox;

use App\Helpers\BotHelper;
use App\Helpers\SwapablesHelper;
use App\R7\Set\AvatarSet;
use App\R7\Set\BanlistSet;
use App\R7\Set\NoticeSet;
use App\R7\Set\PackageSet;
use App\R7\Set\RentalSet;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;
use App\Template\ViewAjax;
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
        $bot_helper = new BotHelper();
        $swapables_helper = new SwapablesHelper();
        $notice_set = new NoticeSet();
        $server_set = new ServerSet();
        $package_set = new PackageSet();

        $message = $input_filter->postFilter("message");
        $max_avatars = $input_filter->postFilter("max_avatars", "integer");
        $source = $input_filter->postFilter("source");
        $source_id = $input_filter->postFilter("source_id", "integer");
        $avatarids = $input_filter->postFilter("avatarids", "array");
        if (count($avatarids) > $max_avatars) {
            $this->failed("To many avatars sent vs what was expected");
            return;
        }
        if ($source == "notice") {
            $rental_set->loadOnField("noticeLink", $source_id);
        } elseif ($source == "server") {
            $stream_set->loadOnField("serverLink", $source_id);
            $rental_set->loadIds($stream_set->getAllIds(), "streamLink");
        } elseif ($source == "package") {
            $rental_set->loadOnField("packageLink", $source_id);
        } elseif ($source == "selectedRental") {
            $rental_set->loadOnField("id", $source_id);
        }
        if ($rental_set->getCount() == 0) {
            $this->failed("No rentals found with selected source/id pair");
            return;
        }
        $stream_set = new StreamSet();
        $stream_set->loadIds($rental_set->getAllByField("streamLink"));
        $avatar_set->loadIds($rental_set->getUniqueArray("avatarLink"));
        $banlist_set->loadIds($rental_set->getUniqueArray("avatarLink"), "avatarLink");
        $banned_ids = $banlist_set->getAllByField("avatarLink");
        $max_avatar_count = $avatar_set->getCount() - $banlist_set->getCount();
        if ($max_avatar_count == 0) {
            $this->failed("No avatars found to send to");
            return;
        }
        $package_set->loadAll();
        $server_set->loadAll();
        $notice_set->loadAll();

        $sent_counter = 0;
        $seen_avatars = [];
        foreach ($rental_set as $rental) {
            if (in_array($rental->getAvatarLink(), $avatarids) == false) {
                continue;
            }
            if (in_array($rental->getAvatarLink(), $seen_avatars) == true) {
                continue;
            }
            $seen_avatars[] = $rental->getAvatarLink();
            $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
            if (in_array($avatar->getId(), $banned_ids) == true) {
                continue;
            }
            $stream = $stream_set->getObjectByID($rental->getStreamLink());
            $package = $package_set->getObjectByID($stream->getPackageLink());
            $server = $server_set->getObjectByID($stream->getServerLink());
            $sendmessage = $swapables_helper->getSwappedText(
                $message,
                $avatar,
                $rental,
                $package,
                $server,
                $stream
            );
            $bot_helper->sendMessage($avatar, $sendmessage, true);
            $sent_counter++;
        }
        $this->ok(sprintf("Sent to %1\$s avatars", $sent_counter));
        $this->setSwapTag("redirect", "outbox");
    }
}
