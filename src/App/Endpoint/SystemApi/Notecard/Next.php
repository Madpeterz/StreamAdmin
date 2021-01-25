<?php

namespace App\Endpoints\SystemApi\Notecard;

use App\Helpers\SwapablesHelper;
use App\Models\Avatar;
use App\Models\Notecard;
use App\Models\NotecardSet;
use App\Models\Notice;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Template;
use App\Template\ViewAjax;

class Next extends ViewAjax
{
    public function process(): void
    {
        $load_ok = false;
        $notecard = new Notecard();
        $rental = new Rental();
        $package = new Package();
        $avatar = new Avatar();
        $template = new Template();
        $stream = new Stream();
        $server = new Server();
        $notice = new Notice();
        $load_by = [
        "rental" => ["notecard" => "rentalLink"],
        "avatar" => ["rental" => "avatarLink"],
        "stream" => ["rental" => "streamLink"],
        "server" => ["stream" => "serverLink"],
        "package" => ["stream" => "packageLink"],
        ];
        if ($notecard->getAsNotice() == false) {
            $load_by["template"] = ["package" => "templateLink"];
        } else {
            $load_by["notice"] = ["rental" => "noticeLink"];
        }
        $notecard_set = new NotecardSet();
        $notecard_set->loadNewest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
        if ($notecard_set->getCount() > 0) {
            $notecard = $notecard_set->getFirst();
            $load_ok = true;
            foreach ($load_by as $objectName => $value) {
                foreach ($value as $source => $linkon) {
                    $object = $$objectName;
                    $loadfromobject = $$source;
                    $loadfromfunction = "get" . ucfirst($linkon);
                    if ($object->loadID($loadfromobject->$loadfromfunction()) == false) {
                        $load_ok = false;
                        break;
                    }
                }
            }
        }
        if ($load_ok == false) {
            $this->setSwapTag("message", "Unable to load notecard right now");
            return;
        }
        $notecard_title = "";
        $notecard_content = "";
        $swap_helper = new SwapablesHelper();
        if ($notecard->getAsNotice() == false) {
            $notecard_title = "Streamdetails for " . $avatar->getAvatarName() . " port: " . $stream->getPort() . "";
            $notecard_content = $swap_helper->get_swapped_text(
                $template->getNotecarddetail(),
                $avatar,
                $rental,
                $package,
                $server,
                $stream
            );
        } else {
            $notecard_title = "Reminder for " . $avatar->getAvatarName() . " port: " . $stream->getPort() . "";
            $notecard_content = $swap_helper->get_swapped_text(
                $notice->getNotecarddetail(),
                $avatar,
                $rental,
                $package,
                $server,
                $stream
            );
        }
        $remove_status = $notecard->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag("message", "Unable to remove old entry");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("AvatarUUID", $avatar->getAvatarUUID());
        $this->setSwapTag("NotecardTitle", $notecard_title);
        $this->setSwapTag("NotecardContent", $notecard_content);
    }
}
