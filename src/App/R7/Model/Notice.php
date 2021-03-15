<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Notice extends genClass
{
    protected $use_table = "notice";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "name" => ["type" => "str", "value" => null],
        "imMessage" => ["type" => "str", "value" => null],
        "useBot" => ["type" => "bool", "value" => 0],
        "sendNotecard" => ["type" => "bool", "value" => 0],
        "notecardDetail" => ["type" => "str", "value" => null],
        "hoursRemaining" => ["type" => "int", "value" => 0],
        "noticeNotecardLink" => ["type" => "int", "value" => 1],
    ];
    public function getName(): ?string
    {
        return $this->getField("name");
    }
    public function getImMessage(): ?string
    {
        return $this->getField("imMessage");
    }
    public function getUseBot(): ?bool
    {
        return $this->getField("useBot");
    }
    public function getSendNotecard(): ?bool
    {
        return $this->getField("sendNotecard");
    }
    public function getNotecardDetail(): ?string
    {
        return $this->getField("notecardDetail");
    }
    public function getHoursRemaining(): ?int
    {
        return $this->getField("hoursRemaining");
    }
    public function getNoticeNotecardLink(): ?int
    {
        return $this->getField("noticeNotecardLink");
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
    * setImMessage
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setImMessage(?string $newvalue): array
    {
        return $this->updateField("imMessage", $newvalue);
    }
    /**
    * setUseBot
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setUseBot(?bool $newvalue): array
    {
        return $this->updateField("useBot", $newvalue);
    }
    /**
    * setSendNotecard
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setSendNotecard(?bool $newvalue): array
    {
        return $this->updateField("sendNotecard", $newvalue);
    }
    /**
    * setNotecardDetail
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setNotecardDetail(?string $newvalue): array
    {
        return $this->updateField("notecardDetail", $newvalue);
    }
    /**
    * setHoursRemaining
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setHoursRemaining(?int $newvalue): array
    {
        return $this->updateField("hoursRemaining", $newvalue);
    }
    /**
    * setNoticeNotecardLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setNoticeNotecardLink(?int $newvalue): array
    {
        return $this->updateField("noticeNotecardLink", $newvalue);
    }
}
// please do not edit this file