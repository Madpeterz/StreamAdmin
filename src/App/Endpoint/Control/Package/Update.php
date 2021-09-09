<?php

namespace App\Endpoint\Control\Package;

use App\R7\Model\Package;
use App\R7\Model\Servertypes;
use App\R7\Model\Template;
use App\R7\Set\NoticenotecardSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    protected Template $template;
    protected Servertypes $servertype;
    protected InputFilter $input;
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
    protected ?string $apiTemplate;
    protected ?int $servertypeLink;
    protected ?int $welcomeNotecardLink;
    protected ?int $setupNotecardLink;

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
        $this->input = new InputFilter();
        $this->package = new Package();
    }

    protected function formData(): void
    {
        $this->name = $this->input->postString("name");
        $this->templateLink = $this->input->postInteger("templateLink");
        $this->cost = $this->input->postInteger("cost");
        $this->days = $this->input->postInteger("days");
        $this->bitrate = $this->input->postInteger("bitrate");
        $this->listeners = $this->input->postInteger("listeners");
        $this->textureSoldout = $this->input->postUUID("textureSoldout");
        $this->textureInstockSmall = $this->input->postUUID("textureInstockSmall");
        $this->textureInstockSelected = $this->input->postUUID("textureInstockSelected");
        $this->autodj = $this->input->postBool("autodj");
        $this->autodjSize = $this->input->postInteger("autodjSize");
        $this->apiTemplate = $this->input->postFilter("apiTemplate");
        $this->servertypeLink = $this->input->postInteger("servertypeLink");
        $this->welcomeNotecardLink = $this->input->postInteger("welcomeNotecardLink");
        $this->setupNotecardLink = $this->input->postInteger("setupNotecardLink");
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
        if (strlen($this->name) < 5) {
            $this->failed("Name length must be 5 or longer");
            return false;
        } elseif (strlen($this->name) > 60) {
            $this->failed("Name must be 30 or less");
            return false;
        } elseif ($this->cost < 1) {
            $this->failed("Cost must be 1 or more");
            return false;
        } elseif ($this->cost > 99999) {
            $this->failed("Cost must be 99999 or less");
            return false;
        } elseif ($this->days < 1) {
            $this->failed("Days must be 1 or more");
            return false;
        } elseif ($this->days > 999) {
            $this->failed("Days must be 999 or less");
            return false;
        } elseif ($this->bitrate < 56) {
            $this->failed("bitrate must be 56 or more");
            return false;
        } elseif ($this->bitrate > 999) {
            $this->failed("bitrate must be 999 or less");
            return false;
        } elseif ($this->listeners < 1) {
            $this->failed("listeners must be 1 or more");
            return false;
        } elseif ($this->listeners > 999) {
            $this->failed("listeners must be 999 or less");
            return false;
        } elseif (strlen($this->textureSoldout) != 36) {
            $this->failed("Texture sold out must be a uuid");
            return false;
        } elseif (strlen($this->textureInstockSmall) != 36) {
            $this->failed("Texture instock small out must be a uuid");
            return false;
        } elseif (strlen($this->textureInstockSelected) != 36) {
            $this->failed("Texture instock selected out must be a uuid");
            return false;
        } elseif ($this->autodjSize > 9999) {
            $this->failed("AutoDJ size must be 9999 or less");
            return false;
        } elseif (strlen($this->apiTemplate) > 50) {
            $this->failed("API template name can not be longer than 50");
            return false;
        } elseif (strlen($this->apiTemplate) < 3) {
            $this->failed("API template name can not be shorter than 3");
            return false;
        }
        return true;
    }

    protected function loadData(): bool
    {
        if ($this->template->loadID($this->templateLink) == false) {
            $this->failed("Unable to find template");
            return false;
        } elseif ($this->servertype->loadID($this->servertypeLink) == false) {
            $this->failed("Unable to find server type");
            return false;
        } elseif ($this->package->loadByPackageUid($this->page) == false) {
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
        $this->package->setApiTemplate($this->apiTemplate);
        $this->package->setServertypeLink($this->servertypeLink);
        $this->package->setWelcomeNotecardLink($this->welcomeNotecardLink);
        $this->package->setSetupNotecardLink($this->setupNotecardLink);
    }

    protected function savePackage(): bool
    {
        $this->updatePackageSettings();
        $update_status = $this->package->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed(
                sprintf(
                    "Unable to update package: %1\$s",
                    $update_status["message"]
                )
            );
            return false;
        }
        return true;
    }
}
