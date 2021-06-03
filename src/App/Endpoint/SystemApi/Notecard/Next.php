<?php

namespace App\Endpoint\SystemApi\Notecard;

use App\Helpers\SwapablesHelper;
use App\R7\Model\Avatar;
use App\R7\Model\Notecard;
use App\R7\Set\NotecardSet;
use App\R7\Model\Notice;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\R7\Model\Template;
use App\Template\SystemApiAjax;

class Next extends SystemApiAjax
{
    protected $notecard = new Notecard();
    protected $rental = new Rental();
    protected $package = new Package();
    protected $avatar = new Avatar();
    protected $template = new Template();
    protected $stream = new Stream();
    protected $server = new Server();
    protected $notice = new Notice();

    protected $notecard_title = "";
    protected $notecard_content = "";

    protected function failedLoad(string $whyfailed): bool
    {
        $this->setSwapTag("message", "Unable to load: " . $whyfailed);
        return false;
    }

    protected function loadContent(): bool
    {
        $swap_helper = new SwapablesHelper();
        if ($this->notecard->getAsNotice() == false) {
            $this->notecard_title = "Streamdetails for "
            . $this->avatar->getAvatarName() . " port: "
            . $this->stream->getPort() . "";
            if ($this->template->getNotecarddetail() == null) {
                $this->setSwapTag("message", "Selected template: " . $this->template->getName() . " is empty");
                return false;
            }
            $this->notecard_content = $swap_helper->getSwappedText(
                $this->template->getNotecarddetail(),
                $this->avatar,
                $this->rental,
                $this->package,
                $this->server,
                $this->stream
            );
            return true;
        }
        $this->notecard_title = "Reminder for "
        . $this->avatar->getAvatarName() . " port: "
        . $this->stream->getPort() . "";
        if ($this->notice->getNotecarddetail() == null) {
            $this->setSwapTag("message", "Selected notice: " . $this->notice->getName() . " is empty");
            return;
        }
        $this->notecard_content = $swap_helper->getSwappedText(
            $this->notice->getNotecarddetail(),
            $this->avatar,
            $this->rental,
            $this->package,
            $this->server,
            $this->stream
        );
    }

    protected function loadData(): bool
    {
        if ($this->rental->loadID($this->notecard->getRentalLink()) == false) {
            return $this->failedLoad("Rental");
        }
        if ($this->avatar->loadID($this->rental->getAvatarLink()) == false) {
            return $this->failedLoad("Avatar");
        }
        if ($this->stream->loadID($this->rental->getStreamLink()) == false) {
            return $this->failedLoad("Stream");
        }
        if ($this->server->loadID($this->stream->getServerLink()) == false) {
            return $this->failedLoad("Server");
        }
        if ($this->package->loadID($this->stream->getPackageLink()) == false) {
            return $this->failedLoad("Package");
        }
        if ($this->notecard->getAsNotice() == true) {
            if ($this->template->loadID($this->package->getTemplateLink()) == false) {
                return $this->failedLoad("Template");
            }
            return;
        }
        if ($this->notice->loadID($this->notecard->getNoticeLink()) == false) {
            return $this->failedLoad("Template");
        }
        return true;
    }
    public function process(): void
    {
        $notecard_set = new NotecardSet();
        $notecard_set->loadNewest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
        if ($notecard_set->getCount() == 0) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "nowork");
            return true;
        }
        $this->notecard = $notecard_set->getFirst();
        if ($this->loadData() == false) {
            return;
        }
        if ($this->loadContent() == false) {
            return;
        }

        $remove_status = $this->notecard->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag("message", "Unable to remove old entry");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("AvatarUUID", $this->avatar->getAvatarUUID());
        $this->setSwapTag("NotecardTitle", $this->notecard_title);
        $this->setSwapTag("NotecardContent", $this->notecard_content);
    }
}
