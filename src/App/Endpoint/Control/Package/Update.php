<?php

namespace App\Endpoint\Control\Package;

use App\Models\Package;
use App\Models\Servertypes;
use App\Models\Template;
use App\Models\Sets\NoticenotecardSet;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    protected Template $template;
    protected Servertypes $servertype;
    protected Package $package;


    protected ?string $name;
    protected ?int $templateLink;
    protected ?int $cost;
    protected ?int $days;
    protected ?int $bitrate;
    protected ?int $listeners;
    protected ?string $textureSoldout;
    protected ?string $textureInstockSmall;
    protected ?string $textureInstockSelected;
    protected ?bool $autodj;
    protected ?int $autodjSize;
    protected ?int $servertypeLink;
    protected ?int $welcomeNotecardLink;
    protected ?int $setupNotecardLink;
    protected ?bool $enableGroupInvite;

    protected array $noticeNotecardIds;

    public function process(): void
    {
        $this->setup();
        $this->formData();
        if ($this->tests() == false) {
            return;
        } elseif ($this->loadData() == false) {
            return;
        } elseif ($this->savePackage() == false) {
            return;
        }
        $this->setSwapTag("redirect", "package");
        $this->ok("Package updated");
    }

    protected function setup(): void
    {
        $noticeNotecards = new NoticenotecardSet();
        $noticeNotecards->loadAll();
        $this->noticeNotecardIds = $noticeNotecards->getAllIds();

        $this->template = new Template();
        $this->servertype = new Servertypes();
        $this->package = new Package();
    }

    protected function formData(): void
    {
        $this->name = $this->input->post("name")->checkStringLength(5, 30)->asString();
        $this->templateLink = $this->input->post("templateLink")->checkGrtThanEq(1)->asInt();
        $this->cost = $this->input->post("cost")->checkInRange(1, 99999)->asInt();
        $this->days = $this->input->post("days")->checkInRange(1, 999)->asInt();
        $this->bitrate = $this->input->post("bitrate")->checkInRange(56, 999)->asInt();
        $this->listeners = $this->input->post("listeners")->checkInRange(1, 999)->asInt();
        $this->textureSoldout = $this->input->post("textureSoldout")->isUuid()->asString();
        $this->textureInstockSmall = $this->input->post("textureInstockSmall")->isUuid()->asString();
        $this->textureInstockSelected = $this->input->post("textureInstockSelected")->isUuid()->asString();
        $this->enableGroupInvite = $this->input->post("enableGroupInvite")->asBool();
        $testing = [
            "name" => $this->name,
            "template" => $this->templateLink,
            "cost" => $this->cost,
            "days" => $this->days,
            "bitrate" => $this->bitrate,
            "listeners" => $this->listeners,
            "texture soldout" => $this->textureSoldout,
            "texture small" => $this->textureInstockSmall,
            "texture selected" => $this->textureInstockSelected,
        ];
        $testing = array_reverse($testing, true);
        foreach ($testing as $key => $value) {
            if ($value === null) {
                $this->failed("Entry: " . $key . " is not set - " . $this->input->getWhyFailed());
                return;
            }
        }


        $this->autodj = $this->input->post("autodj")->asBool();
        $this->autodjSize = $this->input->post("autodjSize")->checkInRange(1, 9999)->asInt();
        $this->servertypeLink = $this->input->post("servertypeLink")->checkGrtThanEq(1)->asInt();
        $this->welcomeNotecardLink = $this->input->post("welcomeNotecardLink")->checkGrtThanEq(1)->asInt();
        $this->setupNotecardLink = $this->input->post("setupNotecardLink")->checkGrtThanEq(1)->asInt();
    }

    protected function tests(): bool
    {
        if (in_array($this->welcomeNotecardLink, $this->noticeNotecardIds) == false) {
            $this->failed("Welcome notecard not selected");
            return false;
        }
        if (in_array($this->setupNotecardLink, $this->noticeNotecardIds) == false) {
            $this->failed("Setup notecard not selected");
            return false;
        }
        if ($this->template->loadID($this->templateLink)->status == false) {
            $this->failed("Unable to find template");
            return false;
        }
        if ($this->servertype->loadID($this->servertypeLink)->status == false) {
            $this->failed("Unable to find server type");
            return false;
        }
        return false;
    }

    protected function loadData(): bool
    {
        if ($this->template->loadID($this->templateLink)->status == false) {
            $this->failed("Unable to find template");
            return false;
        } elseif ($this->servertype->loadID($this->servertypeLink)->status == false) {
            $this->failed("Unable to find server type");
            return false;
        } elseif ($this->package->loadByPackageUid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to load package");
            return false;
        }
        return true;
    }

    protected function updatePackageSettings(): void
    {
        $this->package->setName($this->name);
        $this->package->setAutodj($this->autodj);
        $this->package->setAutodjSize($this->autodjSize);
        $this->package->setListeners($this->listeners);
        $this->package->setBitrate($this->bitrate);
        $this->package->setTemplateLink($this->templateLink);
        $this->package->setCost($this->cost);
        $this->package->setDays($this->days);
        $this->package->setTextureSoldout($this->textureSoldout);
        $this->package->setTextureInstockSmall($this->textureInstockSmall);
        $this->package->setTextureInstockSelected($this->textureInstockSelected);
        $this->package->setServertypeLink($this->servertypeLink);
        $this->package->setWelcomeNotecardLink($this->welcomeNotecardLink);
        $this->package->setSetupNotecardLink($this->setupNotecardLink);
        $this->package->setEnableGroupInvite($this->enableGroupInvite);
    }

    protected function savePackage(): bool
    {
        $this->updatePackageSettings();
        $update_status = $this->package->updateEntry();
        if ($update_status->status == false) {
            $this->failed(
                sprintf(
                    "Unable to update package: %1\$s",
                    $update_status->message
                )
            );
            return false;
        }
        return true;
    }
}
