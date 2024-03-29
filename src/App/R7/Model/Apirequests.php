<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Apirequests extends genClass
{
    protected $use_table = "apirequests";
    // Data Design
    protected $fields = [
        "id",
        "serverLink",
        "rentalLink",
        "streamLink",
        "eventname",
        "attempts",
        "lastAttempt",
        "message",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "serverLink" => ["type" => "int", "value" => null],
        "rentalLink" => ["type" => "int", "value" => null],
        "streamLink" => ["type" => "int", "value" => null],
        "eventname" => ["type" => "str", "value" => null],
        "attempts" => ["type" => "int", "value" => 0],
        "lastAttempt" => ["type" => "int", "value" => 0],
        "message" => ["type" => "str", "value" => null],
    ];
    // Getters
    public function getServerLink(): ?int
    {
        return $this->getField("serverLink");
    }
    public function getRentalLink(): ?int
    {
        return $this->getField("rentalLink");
    }
    public function getStreamLink(): ?int
    {
        return $this->getField("streamLink");
    }
    public function getEventname(): ?string
    {
        return $this->getField("eventname");
    }
    public function getAttempts(): ?int
    {
        return $this->getField("attempts");
    }
    public function getLastAttempt(): ?int
    {
        return $this->getField("lastAttempt");
    }
    public function getMessage(): ?string
    {
        return $this->getField("message");
    }
    // Setters
    /**
    * setServerLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setServerLink(?int $newvalue): array
    {
        return $this->updateField("serverLink", $newvalue);
    }
    /**
    * setRentalLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setRentalLink(?int $newvalue): array
    {
        return $this->updateField("rentalLink", $newvalue);
    }
    /**
    * setStreamLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setStreamLink(?int $newvalue): array
    {
        return $this->updateField("streamLink", $newvalue);
    }
    /**
    * setEventname
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEventname(?string $newvalue): array
    {
        return $this->updateField("eventname", $newvalue);
    }
    /**
    * setAttempts
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAttempts(?int $newvalue): array
    {
        return $this->updateField("attempts", $newvalue);
    }
    /**
    * setLastAttempt
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setLastAttempt(?int $newvalue): array
    {
        return $this->updateField("lastAttempt", $newvalue);
    }
    /**
    * setMessage
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setMessage(?string $newvalue): array
    {
        return $this->updateField("message", $newvalue);
    }
    // Loaders
    public function loadByServerLink(int $serverLink): bool
    {
        return $this->loadByField("serverLink", $serverLink);
    }
    public function loadByRentalLink(int $rentalLink): bool
    {
        return $this->loadByField("rentalLink", $rentalLink);
    }
    public function loadByStreamLink(int $streamLink): bool
    {
        return $this->loadByField("streamLink", $streamLink);
    }
    public function loadByEventname(string $eventname): bool
    {
        return $this->loadByField("eventname", $eventname);
    }
    public function loadByAttempts(int $attempts): bool
    {
        return $this->loadByField("attempts", $attempts);
    }
    public function loadByLastAttempt(int $lastAttempt): bool
    {
        return $this->loadByField("lastAttempt", $lastAttempt);
    }
    public function loadByMessage(string $message): bool
    {
        return $this->loadByField("message", $message);
    }
}
// please do not edit this file
