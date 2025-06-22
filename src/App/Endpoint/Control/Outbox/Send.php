<?php

namespace App\Endpoint\Control\Outbox;

use App\Helpers\BotHelper;
use App\Helpers\SwapablesHelper;
use App\Models\Set\RentalSet;
use App\Models\Set\StreamSet;
use App\Framework\ViewAjax;
use App\Template\ControlAjax;

class Send extends ControlAjax
{
    protected ?RentalSet $rental_set;
    protected array $avatarids = [];
    protected function loadData(): bool
    {
        $this->rental_set = new RentalSet();
        $max_avatars = $this->input->post("max_avatars")->checkGrtThanEq(1)->asInt();
        $source = $this->input->post("source")->asString();
        $source_id = $this->input->post("source_id")->checkGrtThanEq(1)->asInt();
        $this->avatarids = $this->input->post("avatarids")->asArray();
        if (count($this->avatarids) > $max_avatars) {
            $this->failed("To many avatars sent vs what was expected");
            return false;
        }
        if ($source == "Notice") {
            $this->rental_set->loadByNoticeLink($source_id);
        } elseif ($source == "Server") {
            $stream_set = new StreamSet();
            $stream_set->loadByServerLink($source_id);
            $this->rental_set = $stream_set->relatedRental();
        } elseif ($source == "Package") {
            $this->rental_set->loadByPackageLink($source_id);
        } elseif ($source == "Selectedrental") {
            $this->rental_set->loadById($source_id);
        } elseif ($source == "Clients") {
            $this->rental_set->loadAll();
        }
        if ($this->rental_set->getCount() == 0) {
            $this->failed("No rentals found with selected source/id pair");
            return false;
        }
        return true;
    }
    public function process(): void
    {
        $message = $this->input->post("message")->asString();
        $this->loadData();

        $stream_set = $this->rental_set->relatedStream();
        $avatar_set = $this->rental_set->relatedAvatar();
        $banlist_set = $avatar_set->relatedBanlist();
        $banned_ids = $banlist_set->uniqueAvatarLinks();
        $max_avatar_count = $avatar_set->getCount() - $banlist_set->getCount();
        if ($max_avatar_count == 0) {
            $this->failed("No avatars found to send to");
            return;
        }
        $package_set = $stream_set->relatedPackage();
        $server_set = $stream_set->relatedServer();

        $bot_helper = new BotHelper();
        $swapables_helper = new SwapablesHelper();

        $sent_counter = 0;
        $seen_avatars = [];
        $hadError = false;
        foreach ($this->rental_set as $rental) {
            if (in_array($rental->getAvatarLink(), $this->avatarids) == false) {
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
            $sendCreateStatus = $bot_helper->sendMessage($avatar, $sendmessage, true);
            if ($sendCreateStatus->status == false) {
                $hadError = true;
                $this->failed($sendCreateStatus->message);
                break;
            }
            $sent_counter++;
        }
        if ($hadError == true) {
            return;
        }
        $this->redirectWithMessage(sprintf("Sent to %1\$s avatars", $sent_counter));
        $this->createAuditLog(null, "send mail", "total avatars", $sent_counter);
    }
}
