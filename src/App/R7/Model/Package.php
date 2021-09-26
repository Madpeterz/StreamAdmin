<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Package extends genClass
{
    protected $use_table = "package";
    // Data Design
    protected $fields = [
        "id",
        "packageUid",
        "name",
        "autodj",
        "autodjSize",
        "listeners",
        "bitrate",
        "templateLink",
        "servertypeLink",
        "cost",
        "days",
        "textureSoldout",
        "textureInstockSmall",
        "textureInstockSelected",
        "apiTemplate",
        "welcomeNotecardLink",
        "setupNotecardLink",
        "enableGroupInvite",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "packageUid" => ["type" => "str", "value" => null],
        "name" => ["type" => "str", "value" => null],
        "autodj" => ["type" => "bool", "value" => 0],
        "autodjSize" => ["type" => "str", "value" => null],
        "listeners" => ["type" => "int", "value" => null],
        "bitrate" => ["type" => "int", "value" => null],
        "templateLink" => ["type" => "int", "value" => null],
        "servertypeLink" => ["type" => "int", "value" => 1],
        "cost" => ["type" => "int", "value" => null],
        "days" => ["type" => "int", "value" => null],
        "textureSoldout" => ["type" => "str", "value" => null],
        "textureInstockSmall" => ["type" => "str", "value" => null],
        "textureInstockSelected" => ["type" => "str", "value" => null],
        "apiTemplate" => ["type" => "str", "value" => null],
        "welcomeNotecardLink" => ["type" => "int", "value" => 1],
        "setupNotecardLink" => ["type" => "int", "value" => 1],
        "enableGroupInvite" => ["type" => "bool", "value" => 1],
    ];
    // Getters
    public function getPackageUid(): ?string
    {
        return $this->getField("packageUid");
    }
    public function getName(): ?string
    {
        return $this->getField("name");
    }
    public function getAutodj(): ?bool
    {
        return $this->getField("autodj");
    }
    public function getAutodjSize(): ?string
    {
        return $this->getField("autodjSize");
    }
    public function getListeners(): ?int
    {
        return $this->getField("listeners");
    }
    public function getBitrate(): ?int
    {
        return $this->getField("bitrate");
    }
    public function getTemplateLink(): ?int
    {
        return $this->getField("templateLink");
    }
    public function getServertypeLink(): ?int
    {
        return $this->getField("servertypeLink");
    }
    public function getCost(): ?int
    {
        return $this->getField("cost");
    }
    public function getDays(): ?int
    {
        return $this->getField("days");
    }
    public function getTextureSoldout(): ?string
    {
        return $this->getField("textureSoldout");
    }
    public function getTextureInstockSmall(): ?string
    {
        return $this->getField("textureInstockSmall");
    }
    public function getTextureInstockSelected(): ?string
    {
        return $this->getField("textureInstockSelected");
    }
    public function getApiTemplate(): ?string
    {
        return $this->getField("apiTemplate");
    }
    public function getWelcomeNotecardLink(): ?int
    {
        return $this->getField("welcomeNotecardLink");
    }
    public function getSetupNotecardLink(): ?int
    {
        return $this->getField("setupNotecardLink");
    }
    public function getEnableGroupInvite(): ?bool
    {
        return $this->getField("enableGroupInvite");
    }
    // Setters
    /**
    * setPackageUid
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setPackageUid(?string $newvalue): array
    {
        return $this->updateField("packageUid", $newvalue);
    }
    /**
    * setName
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setName(?string $newvalue): array
    {
        return $this->updateField("name", $newvalue);
    }
    /**
    * setAutodj
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAutodj(?bool $newvalue): array
    {
        return $this->updateField("autodj", $newvalue);
    }
    /**
    * setAutodjSize
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAutodjSize(?string $newvalue): array
    {
        return $this->updateField("autodjSize", $newvalue);
    }
    /**
    * setListeners
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setListeners(?int $newvalue): array
    {
        return $this->updateField("listeners", $newvalue);
    }
    /**
    * setBitrate
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setBitrate(?int $newvalue): array
    {
        return $this->updateField("bitrate", $newvalue);
    }
    /**
    * setTemplateLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setTemplateLink(?int $newvalue): array
    {
        return $this->updateField("templateLink", $newvalue);
    }
    /**
    * setServertypeLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setServertypeLink(?int $newvalue): array
    {
        return $this->updateField("servertypeLink", $newvalue);
    }
    /**
    * setCost
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setCost(?int $newvalue): array
    {
        return $this->updateField("cost", $newvalue);
    }
    /**
    * setDays
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setDays(?int $newvalue): array
    {
        return $this->updateField("days", $newvalue);
    }
    /**
    * setTextureSoldout
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setTextureSoldout(?string $newvalue): array
    {
        return $this->updateField("textureSoldout", $newvalue);
    }
    /**
    * setTextureInstockSmall
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setTextureInstockSmall(?string $newvalue): array
    {
        return $this->updateField("textureInstockSmall", $newvalue);
    }
    /**
    * setTextureInstockSelected
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setTextureInstockSelected(?string $newvalue): array
    {
        return $this->updateField("textureInstockSelected", $newvalue);
    }
    /**
    * setApiTemplate
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setApiTemplate(?string $newvalue): array
    {
        return $this->updateField("apiTemplate", $newvalue);
    }
    /**
    * setWelcomeNotecardLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setWelcomeNotecardLink(?int $newvalue): array
    {
        return $this->updateField("welcomeNotecardLink", $newvalue);
    }
    /**
    * setSetupNotecardLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setSetupNotecardLink(?int $newvalue): array
    {
        return $this->updateField("setupNotecardLink", $newvalue);
    }
    /**
    * setEnableGroupInvite
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEnableGroupInvite(?bool $newvalue): array
    {
        return $this->updateField("enableGroupInvite", $newvalue);
    }
    // Loaders
    public function loadByPackageUid(string $packageUid): bool
    {
        return $this->loadByField("packageUid", $packageUid);
    }
    public function loadByName(string $name): bool
    {
        return $this->loadByField("name", $name);
    }
    public function loadByAutodj(bool $autodj): bool
    {
        return $this->loadByField("autodj", $autodj);
    }
    public function loadByAutodjSize(string $autodjSize): bool
    {
        return $this->loadByField("autodjSize", $autodjSize);
    }
    public function loadByListeners(int $listeners): bool
    {
        return $this->loadByField("listeners", $listeners);
    }
    public function loadByBitrate(int $bitrate): bool
    {
        return $this->loadByField("bitrate", $bitrate);
    }
    public function loadByTemplateLink(int $templateLink): bool
    {
        return $this->loadByField("templateLink", $templateLink);
    }
    public function loadByServertypeLink(int $servertypeLink): bool
    {
        return $this->loadByField("servertypeLink", $servertypeLink);
    }
    public function loadByCost(int $cost): bool
    {
        return $this->loadByField("cost", $cost);
    }
    public function loadByDays(int $days): bool
    {
        return $this->loadByField("days", $days);
    }
    public function loadByTextureSoldout(string $textureSoldout): bool
    {
        return $this->loadByField("textureSoldout", $textureSoldout);
    }
    public function loadByTextureInstockSmall(string $textureInstockSmall): bool
    {
        return $this->loadByField("textureInstockSmall", $textureInstockSmall);
    }
    public function loadByTextureInstockSelected(string $textureInstockSelected): bool
    {
        return $this->loadByField("textureInstockSelected", $textureInstockSelected);
    }
    public function loadByApiTemplate(string $apiTemplate): bool
    {
        return $this->loadByField("apiTemplate", $apiTemplate);
    }
    public function loadByWelcomeNotecardLink(int $welcomeNotecardLink): bool
    {
        return $this->loadByField("welcomeNotecardLink", $welcomeNotecardLink);
    }
    public function loadBySetupNotecardLink(int $setupNotecardLink): bool
    {
        return $this->loadByField("setupNotecardLink", $setupNotecardLink);
    }
    public function loadByEnableGroupInvite(bool $enableGroupInvite): bool
    {
        return $this->loadByField("enableGroupInvite", $enableGroupInvite);
    }
}
// please do not edit this file
