<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\BanlistSet as BanlistSet;
use App\Models\Sets\BotconfigSet as BotconfigSet;
use App\Models\Sets\MessageSet as MessageSet;
use App\Models\Sets\ObjectsSet as ObjectsSet;
use App\Models\Sets\RentalSet as RentalSet;
use App\Models\Sets\ResellerSet as ResellerSet;
use App\Models\Sets\SlconfigSet as SlconfigSet;
use App\Models\Sets\StaffSet as StaffSet;
use App\Models\Sets\TransactionsSet as TransactionsSet;

// Do not edit this file, rerun gen.php to update!
class Avatar extends genClass
{
    protected $use_table = "avatar";
    // Data Design
    protected $fields = [
        "id",
        "avatarUUID",
        "avatarName",
        "avatarUid",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "avatarUUID" => ["type" => "str", "value" => null],
        "avatarName" => ["type" => "str", "value" => null],
        "avatarUid" => ["type" => "str", "value" => null],
    ];
    // Setters
    /**
    * setAvatarUUID
    */
    public function setAvatarUUID(?string $newValue): UpdateReply
    {
        return $this->updateField("avatarUUID", $newValue);
    }
    /**
    * setAvatarName
    */
    public function setAvatarName(?string $newValue): UpdateReply
    {
        return $this->updateField("avatarName", $newValue);
    }
    /**
    * setAvatarUid
    */
    public function setAvatarUid(?string $newValue): UpdateReply
    {
        return $this->updateField("avatarUid", $newValue);
    }
    // Getters
    public function getAvatarUUID(): ?string
    {
        return $this->getField("avatarUUID");
    }
    public function getAvatarName(): ?string
    {
        return $this->getField("avatarName");
    }
    public function getAvatarUid(): ?string
    {
        return $this->getField("avatarUid");
    }
    // Loaders
    public function loadByAvatarUUID(string $avatarUUID): SingleLoadReply
    {
        return $this->loadByField(
            "avatarUUID",
            $avatarUUID
        );
    }
    public function loadByAvatarName(string $avatarName): SingleLoadReply
    {
        return $this->loadByField(
            "avatarName",
            $avatarName
        );
    }
    public function loadByAvatarUid(string $avatarUid): SingleLoadReply
    {
        return $this->loadByField(
            "avatarUid",
            $avatarUid
        );
    }
    public function relatedBanlist(): BanlistSet
    {
        $ids = [$this->getId()];
        $collection = new BanlistSet();
        $collection->loadFromAvatarLinks($ids);
        return $collection;
    }
    public function relatedBotconfig(): BotconfigSet
    {
        $ids = [$this->getId()];
        $collection = new BotconfigSet();
        $collection->loadFromAvatarLinks($ids);
        return $collection;
    }
    public function relatedMessage(): MessageSet
    {
        $ids = [$this->getId()];
        $collection = new MessageSet();
        $collection->loadFromAvatarLinks($ids);
        return $collection;
    }
    public function relatedObjects(): ObjectsSet
    {
        $ids = [$this->getId()];
        $collection = new ObjectsSet();
        $collection->loadFromAvatarLinks($ids);
        return $collection;
    }
    public function relatedRental(): RentalSet
    {
        $ids = [$this->getId()];
        $collection = new RentalSet();
        $collection->loadFromAvatarLinks($ids);
        return $collection;
    }
    public function relatedReseller(): ResellerSet
    {
        $ids = [$this->getId()];
        $collection = new ResellerSet();
        $collection->loadFromAvatarLinks($ids);
        return $collection;
    }
    public function relatedSlconfig(): SlconfigSet
    {
        $ids = [$this->getId()];
        $collection = new SlconfigSet();
        $collection->loadFromOwnerAvatarLinks($ids);
        return $collection;
    }
    public function relatedStaff(): StaffSet
    {
        $ids = [$this->getId()];
        $collection = new StaffSet();
        $collection->loadFromAvatarLinks($ids);
        return $collection;
    }
    public function relatedTransactions(): TransactionsSet
    {
        $ids = [$this->getId()];
        $collection = new TransactionsSet();
        $collection->loadFromAvatarLinks($ids);
        return $collection;
    }
}
// please do not edit this file
