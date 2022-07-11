<?php

namespace App\Endpoint\SystemApi\Notecard;

use App\Helpers\SwapablesHelper;
use App\Models\Avatar;
use App\Models\Notecard;
use App\Models\Sets\NotecardSet;
use App\Models\Notice;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Template;
use App\Template\SystemApiAjax;

class Next extends SystemApiAjax
{
    protected Notecard $notecard;
    protected ?Rental $rental;
    protected ?Package $package;
    protected ?Avatar $avatar;
    protected ?Template $template;
    protected ?Stream $stream;
    protected ?Server $server;
    protected ?Notice $notice;

    protected $notecard_title = "";
    protected $notecard_content = "";

    protected function failedLoad(string $whyfailed): bool
    {
        $this->failed("Unable to load: " . $whyfailed);
        return false;
    }

    protected function asNotice(): bool
    {
        $swap_helper = new SwapablesHelper();
        $this->notecard_title = "Streamdetails for "
        . $this->avatar->getAvatarName() . " port: "
        . $this->stream->getPort() . "";
        if ($this->template->getNotecarddetail() == null) {
            $this->failed("Selected template: " . $this->template->getName() . " is empty");
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

    protected function asNotecard(): bool
    {
        $swap_helper = new SwapablesHelper();
        $this->notecard_title = "Reminder for "
        . $this->avatar->getAvatarName() . " port: "
        . $this->stream->getPort() . "";
        if ($this->notice->getNotecarddetail() == null) {
            $this->failed("Selected notice: " . $this->notice->getName() . " is empty");
            return false;
        }
        $this->notecard_content = $swap_helper->getSwappedText(
            $this->notice->getNotecarddetail(),
            $this->avatar,
            $this->rental,
            $this->package,
            $this->server,
            $this->stream
        );
        return true;
    }

    protected function loadContent(): bool
    {

        if ($this->notecard->getAsNotice() == false) {
            return $this->asNotice();
        }
        return $this->asNotecard();
    }

    protected function loadData(): bool
    {
        $this->notice = new Notice();

        $this->rental = $this->notecard->relatedRental()->getFirst();
        $this->avatar = $this->rental?->relatedAvatar()->getFirst();
        $this->stream = $this->rental?->relatedStream()->getFirst();
        $this->server = $this->stream?->relatedServer()->getFirst();
        $this->package = $this->rental?->relatedPackage()->getFirst();
        $test = [$this->rental,$this->avatar,$this->stream,$this->server,$this->package];
        if (in_array(null, $test) == true) {
            $this->failed("One or more required objects did not load!");
            return false;
        }
        if ($this->notecard->getAsNotice() == false) {
            $this->template = $this->package->relatedTemplate()->getFirst();
            return true;
        }
        if ($this->notice->loadID($this->notecard->getNoticeLink()) == false) {
            return $this->failedLoad("Notice");
        }
        return true;
    }
    public function process(): void
    {
        $this->notecard = new Notecard();
        $notecard_set = new NotecardSet();
        $notecard_set->loadNewest(limit:1, orderDirection:"ASC");
        if ($notecard_set->getCount() == 0) {
            $this->ok("nowork");
            return;
        }
        $this->notecard = $notecard_set->getFirst();
        if ($this->loadData() == false) {
            return;
        }
        if ($this->loadContent() == false) {
            return;
        }

        $remove_status = $this->notecard->removeEntry();
        if ($remove_status->status == false) {
            $this->failed("Unable to remove old entry");
            return;
        }
        $this->ok("ok");
        $this->setSwapTag("AvatarUUID", $this->avatar->getAvatarUUID());
        $this->setSwapTag("NotecardTitle", $this->notecard_title);
        $this->setSwapTag("NotecardContent", $this->notecard_content);
    }
}
