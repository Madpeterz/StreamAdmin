<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\AvatarSet as AvatarSet;
use App\Models\Sets\PackageSet as PackageSet;
use App\Models\Sets\RegionSet as RegionSet;
use App\Models\Sets\ResellerSet as ResellerSet;
use App\Models\Sets\StreamSet as StreamSet;

// Do not edit this file, rerun gen.php to update!
class Transactions extends genClass
{
    protected $use_table = "transactions";
    // Data Design
    protected $fields = [
        "id",
        "avatarLink",
        "packageLink",
        "streamLink",
        "resellerLink",
        "regionLink",
        "amount",
        "unixtime",
        "transactionUid",
        "renew",
        "SLtransactionUUID",
        "ViaHud",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "avatarLink" => ["type" => "int", "value" => null],
        "packageLink" => ["type" => "int", "value" => null],
        "streamLink" => ["type" => "int", "value" => null],
        "resellerLink" => ["type" => "int", "value" => null],
        "regionLink" => ["type" => "int", "value" => null],
        "amount" => ["type" => "int", "value" => null],
        "unixtime" => ["type" => "int", "value" => null],
        "transactionUid" => ["type" => "str", "value" => null],
        "renew" => ["type" => "bool", "value" => 0],
        "SLtransactionUUID" => ["type" => "str", "value" => null],
        "ViaHud" => ["type" => "bool", "value" => 0],
    ];
    // Setters
    /**
    * setAvatarLink
    */
    public function setAvatarLink(?int $newValue): UpdateReply
    {
        return $this->updateField("avatarLink", $newValue);
    }
    /**
    * setPackageLink
    */
    public function setPackageLink(?int $newValue): UpdateReply
    {
        return $this->updateField("packageLink", $newValue);
    }
    /**
    * setStreamLink
    */
    public function setStreamLink(?int $newValue): UpdateReply
    {
        return $this->updateField("streamLink", $newValue);
    }
    /**
    * setResellerLink
    */
    public function setResellerLink(?int $newValue): UpdateReply
    {
        return $this->updateField("resellerLink", $newValue);
    }
    /**
    * setRegionLink
    */
    public function setRegionLink(?int $newValue): UpdateReply
    {
        return $this->updateField("regionLink", $newValue);
    }
    /**
    * setAmount
    */
    public function setAmount(?int $newValue): UpdateReply
    {
        return $this->updateField("amount", $newValue);
    }
    /**
    * setUnixtime
    */
    public function setUnixtime(?int $newValue): UpdateReply
    {
        return $this->updateField("unixtime", $newValue);
    }
    /**
    * setTransactionUid
    */
    public function setTransactionUid(?string $newValue): UpdateReply
    {
        return $this->updateField("transactionUid", $newValue);
    }
    /**
    * setRenew
    */
    public function setRenew(?bool $newValue): UpdateReply
    {
        return $this->updateField("renew", $newValue);
    }
    /**
    * setSLtransactionUUID
    */
    public function setSLtransactionUUID(?string $newValue): UpdateReply
    {
        return $this->updateField("SLtransactionUUID", $newValue);
    }
    /**
    * setViaHud
    */
    public function setViaHud(?bool $newValue): UpdateReply
    {
        return $this->updateField("ViaHud", $newValue);
    }
    // Getters
    public function getAvatarLink(): ?int
    {
        return $this->getField("avatarLink");
    }
    public function getPackageLink(): ?int
    {
        return $this->getField("packageLink");
    }
    public function getStreamLink(): ?int
    {
        return $this->getField("streamLink");
    }
    public function getResellerLink(): ?int
    {
        return $this->getField("resellerLink");
    }
    public function getRegionLink(): ?int
    {
        return $this->getField("regionLink");
    }
    public function getAmount(): ?int
    {
        return $this->getField("amount");
    }
    public function getUnixtime(): ?int
    {
        return $this->getField("unixtime");
    }
    public function getTransactionUid(): ?string
    {
        return $this->getField("transactionUid");
    }
    public function getRenew(): ?bool
    {
        return $this->getField("renew");
    }
    public function getSLtransactionUUID(): ?string
    {
        return $this->getField("SLtransactionUUID");
    }
    public function getViaHud(): ?bool
    {
        return $this->getField("ViaHud");
    }
    // Loaders
    public function loadByAvatarLink(int $avatarLink): SingleLoadReply
    {
        return $this->loadByField(
            "avatarLink",
            $avatarLink
        );
    }
    public function loadByPackageLink(int $packageLink): SingleLoadReply
    {
        return $this->loadByField(
            "packageLink",
            $packageLink
        );
    }
    public function loadByStreamLink(int $streamLink): SingleLoadReply
    {
        return $this->loadByField(
            "streamLink",
            $streamLink
        );
    }
    public function loadByResellerLink(int $resellerLink): SingleLoadReply
    {
        return $this->loadByField(
            "resellerLink",
            $resellerLink
        );
    }
    public function loadByRegionLink(int $regionLink): SingleLoadReply
    {
        return $this->loadByField(
            "regionLink",
            $regionLink
        );
    }
    public function loadByAmount(int $amount): SingleLoadReply
    {
        return $this->loadByField(
            "amount",
            $amount
        );
    }
    public function loadByUnixtime(int $unixtime): SingleLoadReply
    {
        return $this->loadByField(
            "unixtime",
            $unixtime
        );
    }
    public function loadByTransactionUid(string $transactionUid): SingleLoadReply
    {
        return $this->loadByField(
            "transactionUid",
            $transactionUid
        );
    }
    public function loadByRenew(bool $renew): SingleLoadReply
    {
        return $this->loadByField(
            "renew",
            $renew
        );
    }
    public function loadBySLtransactionUUID(string $SLtransactionUUID): SingleLoadReply
    {
        return $this->loadByField(
            "SLtransactionUUID",
            $SLtransactionUUID
        );
    }
    public function loadByViaHud(bool $ViaHud): SingleLoadReply
    {
        return $this->loadByField(
            "ViaHud",
            $ViaHud
        );
    }
    public function relatedAvatar(?array $limitFields = null): AvatarSet
    {
        $ids = [$this->getAvatarLink()];
        $collection = new AvatarSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedPackage(?array $limitFields = null): PackageSet
    {
        $ids = [$this->getPackageLink()];
        $collection = new PackageSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedRegion(?array $limitFields = null): RegionSet
    {
        $ids = [$this->getRegionLink()];
        $collection = new RegionSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedReseller(?array $limitFields = null): ResellerSet
    {
        $ids = [$this->getResellerLink()];
        $collection = new ResellerSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedStream(?array $limitFields = null): StreamSet
    {
        $ids = [$this->getStreamLink()];
        $collection = new StreamSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
}
// please do not edit this file
