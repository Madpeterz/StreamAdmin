<?php

namespace App\Endpoint\Control\Package;

use App\R7\Model\Package;
use App\R7\Model\Servertypes;
use App\R7\Model\Template;
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
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Package updated");
    }

    protected function setup(): void
    {
        $this->template = new Template();
        $this->servertype = new Servertypes();
        $this->input = new InputFilter();
        $this->package = new Package();
    }

    protected function formData(): void
    {
        $this->name = $this->input->postFilter("name");
        $this->templateLink = $this->input->postFilter("templateLink", "integer");
        $this->cost = $this->input->postFilter("cost", "integer");
        $this->days = $this->input->postFilter("days", "integer");
        $this->bitrate = $this->input->postFilter("bitrate", "integer");
        $this->listeners = $this->input->postFilter("listeners", "integer");
        $this->textureSoldout = $this->input->postFilter("textureSoldout", "uuid");
        $this->textureInstockSmall = $this->input->postFilter("textureInstockSmall", "uuid");
        $this->textureInstockSelected = $this->input->postFilter("textureInstockSelected", "uuid");
        $this->autodj = $this->input->postFilter("autodj", "bool");
        $this->autodjSize = $this->input->postFilter("autodjSize", "integer");
        $this->apiTemplate = $this->input->postFilter("apiTemplate");
        $this->servertypeLink = $this->input->postFilter("servertypeLink", "integer");
    }

    protected function tests(): bool
    {
        if (strlen($this->name) < 5) {
            $this->setSwapTag("message", "Name length must be 5 or longer");
            return false;
        } elseif (strlen($this->name) > 60) {
            $this->setSwapTag("message", "Name must be 30 or less");
            return false;
        } elseif ($this->cost < 1) {
            $this->setSwapTag("message", "Cost must be 1 or more");
            return false;
        } elseif ($this->cost > 99999) {
            $this->setSwapTag("message", "Cost must be 99999 or less");
            return false;
        } elseif ($this->days < 1) {
            $this->setSwapTag("message", "Days must be 1 or more");
            return false;
        } elseif ($this->days > 999) {
            $this->setSwapTag("message", "Days must be 999 or less");
            return false;
        } elseif ($this->bitrate < 56) {
            $this->setSwapTag("message", "bitrate must be 56 or more");
            return false;
        } elseif ($this->bitrate > 999) {
            $this->setSwapTag("message", "bitrate must be 999 or less");
            return false;
        } elseif ($this->listeners < 1) {
            $this->setSwapTag("message", "listeners must be 1 or more");
            return false;
        } elseif ($this->listeners > 999) {
            $this->setSwapTag("message", "listeners must be 999 or less");
            return false;
        } elseif (strlen($this->textureSoldout) != 36) {
            $this->setSwapTag("message", "Texture sold out must be a uuid");
            return false;
        } elseif (strlen($this->textureInstockSmall) != 36) {
            $this->setSwapTag("message", "Texture instock small out must be a uuid");
            return false;
        } elseif (strlen($this->textureInstockSelected) != 36) {
            $this->setSwapTag("message", "Texture instock selected out must be a uuid");
            return false;
        } elseif ($this->autodjSize > 9999) {
            $this->setSwapTag("message", "AutoDJ size must be 9999 or less");
            return false;
        } elseif (strlen($this->apiTemplate) > 50) {
            $this->setSwapTag("message", "API template name can not be longer than 50");
            return false;
        } elseif (strlen($this->apiTemplate) < 3) {
            $this->setSwapTag("message", "API template name can not be shorter than 3");
            return false;
        }
        return true;
    }

    protected function loadData(): bool
    {
        if ($this->template->loadID($this->templateLink) == false) {
            $this->setSwapTag("message", "Unable to find template");
            return false;
        } elseif ($this->servertype->loadID($this->servertypeLink) == false) {
            $this->setSwapTag("message", "Unable to find server type");
            return false;
        } elseif ($this->package->loadByField("packageUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to load package");
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
    }

    protected function savePackage(): bool
    {
        $this->updatePackageSettings();
        $update_status = $this->package->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
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
