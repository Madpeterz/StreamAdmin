<?php

namespace App\Template;

use App\Config;
use App\Helpers\AvatarHelper;
use App\Helpers\ObjectHelper;
use App\Helpers\RegionHelper;
use App\Helpers\ResellerHelper;
use App\Helpers\TransactionsHelper;
use App\Models\Avatar;
use App\Models\Botconfig;
use App\Models\Message;
use App\Models\Objects;
use App\Models\Package;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\Stream;
use YAPF\Bootstrap\Template\ViewAjax as TemplateViewAjax;
use YAPF\Framework\Responses\DbObjects\CreateReply;
use YAPF\InputFilter\InputFilter;

abstract class SecondlifeAjax extends TemplateViewAjax
{
    protected bool $trackObject = true;
    protected $version = "";
    protected $mode = "";
    protected $objectuuid = "";
    protected $regionname = "";
    protected $ownerkey = "";
    protected $ownername = "";
    protected $pos = "";
    protected $objectname = "";
    protected $objecttype = "";
    protected $hash = "";
    protected $unixtime = 0;

    protected bool $load_ok = true;
    protected $staticpart = "";

    protected ?Avatar $Object_OwnerAvatar;
    protected ?Region $region;
    protected ?Reseller $reseller;
    protected bool $owner_override = false;
    protected ?Objects $object;
    protected bool $soft_fail = false;
    protected InputFilter $input;
    protected Config $siteConfig;

    protected ?Botconfig $botconfig = null;
    protected ?Avatar $bot = null;

    protected function useCredits(
        Avatar $avatar_system,
        Avatar $avatar,
        Package $package,
        Stream $stream,
        int $amountpaid,
        bool $renewal = false
    ): bool {
        $this->setSwapTag("credit-return", 0);
        $this->setSwapTag("credit-remaining", 0);
        $resellerAv = $this->reseller->relatedAvatar()->getFirst();
        if ($resellerAv == null) {
            return true;
        }
        if ($resellerAv->getId() != $avatar_system->getId()) {
            return true; // credits can only be used at system owner venders
        }
        if ($avatar->getCredits() <= 0) {
            return true; // no credits on account
        }
        // use credits and refund
        $refund = $avatar->getCredits(); // refund remaining balance
        if ($avatar->getCredits() > $amountpaid) {
            $refund = $amountpaid; // refund just the payment
        }
        $newbalance = $avatar->getCredits() - $refund;
        if ($newbalance < 0) {
            $this->failed("Attempting to refund more than expected");
            return false;
        }
        // update balance
        $avatar->setCredits($newbalance);
        $update = $avatar->updateEntry();
        if ($update->status == false) {
            $this->failed("Unable to update avatar balance");
            return false;
        }

        $TransactionsHelper = new TransactionsHelper();

        $status = $TransactionsHelper->createTransaction(
            avatar: $avatar,
            package: $package,
            stream: $stream,
            reseller: $this->reseller,
            region: $this->region,
            amountpaid: 0 - $refund,
            notes: "refund of prepaid credits",
            renewal: $renewal
        );
        if ($status == false) {
            $this->failed("Unable to create transaction for credits: " . $TransactionsHelper->whyfailed);
            return false;
        }
        $this->setSwapTag("credit-return", $refund);
        $this->setSwapTag("credit-remaining", $newbalance);
        return true;
    }
    protected function setupBot(): bool
    {
        if ($this->botconfig == null) {
            $this->botconfig = new Botconfig();
            $this->botconfig->loadID(1);
        }
        if ($this->botconfig->isLoaded() == false) {
            $this->failed("Unable to load bot config");
            return false;
        }
        if ($this->bot == null) {
            $this->bot = new Avatar();
            $this->bot->loadID($this->botconfig->getAvatarLink());
        }
        if ($this->bot->isLoaded() == false) {
            $this->bot = null;
            $this->failed("Unable to load bot avatar config");
            return false;
        }
        return true;
    }
    protected function sendMessageToAvatar(Avatar $av, string $sendmessage): CreateReply
    {
        $message = new Message();
        $message->setAvatarLink($av->getId());
        $message->setMessage($sendmessage);
        return $message->createEntry();
    }

    public function renderPage(): void
    {
        $output = $this->getOutputObject();
        $tags = $output->getAllTags();
        foreach ($tags as $tagname => $tagvalue) {
            if (is_bool($tagvalue) == false) {
                continue;
            }
            if ($tagvalue == true) {
                $this->setSwapTag($tagname, "1");
                continue;
            }
            $this->setSwapTag($tagname, "0");
        }
        parent::renderPage();
    }
    public function captureOutput(): string
    {
        $output = $this->getOutputObject();
        $tags = $output->getAllTags();
        foreach ($tags as $tagname => $tagvalue) {
            if (is_bool($tagvalue) == false) {
                continue;
            }
            if ($tagvalue == true) {
                $this->setSwapTag($tagname, "1");
                continue;
            }
            $this->setSwapTag($tagname, "0");
        }
        $this->setSwapTag("render", "Ajax");
        return json_encode($output->getAllTags());
    }
    public function setReseller(Reseller $reseller): void
    {
        $this->reseller = $reseller;
    }
    public function setOwnerOverride(bool $status): void
    {
        $this->owner_override = $status;
    }
    public function setRegion(Region $region): void
    {
        $this->region = $region;
    }
    public function getSoftFail(): bool
    {
        return $this->soft_fail;
    }
    public function getLoadOk(): bool
    {
        return $this->load_ok;
    }

    public function __construct(bool $AutoLoadTemplate = false, bool $bypassHash = false)
    {
        parent::__construct($AutoLoadTemplate);
        global $system;
        $this->siteConfig = $system;
        $this->input = new InputFilter();
        $this->requiredValues();
        $this->timeWindow();
        if ($bypassHash == false) {
            $this->hashCheck();
        }
        $this->versionCheck();
        if ($this->load_ok == false) {
            $this->setSwapTag("status", false);
            return;
        }
        $this->failed("ready");
    }

    protected function versionCheck(): void
    {
        $min_version = "2.0.1.0";
        if (version_compare($this->version, $min_version, ">=") == false) {
            $this->load_ok = false;
            $this->failed("Requires version: " . $min_version . " or higher given version " . $this->version);
            return;
        }
    }

    protected function requiredValues(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $required_sl = [
            "version" => "s",
            "mode" => "s",
            "objectuuid" => "k",
            "regionname" => "s",
            "ownerkey" => "k",
            "ownername" => "s",
            "pos" => "s",
            "objectname" => "s",
            "objecttype" => "s",
        ];

        $this->staticpart = $this->config->getModule() . "" . $this->config->getArea();

        foreach ($required_sl as $fieldname => $typematch) {
            $value = $this->input->post($fieldname)->checkStringLengthMin(1)->asString();
            if ($typematch == "k") {
                $value = $this->input->post($fieldname)->isUuid()->asString();
            }
            if ($value === null) {
                $this->load_ok = false;
                $this->failed("Value: " . $fieldname . " is missing");
                return;
            }
            $this->$fieldname = $value;
            $this->staticpart .= $value;
        }

        $this->unixtime = $this->input->post("unixtime")->asInt();
        if ($this->unixtime === null) {
            $this->failed("Missing unixtime value");
            $this->load_ok = false;
            return;
        }
        $this->hash = $this->input->post("hash")->asString();
        if ($this->hash === null) {
            $this->load_ok = false;
            $this->failed("Missing hash value");
            return;
        }
    }

    protected function hashCheck(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $raw = $this->unixtime . "" . $this->staticpart . "" . $this->siteConfig->getSlConfig()->getSlLinkCode();
        $hashcheck = sha1($raw);
        if ($hashcheck != $this->hash) {
            $this->load_ok = false;
            $this->failed("Unable to vaildate request to API endpoint");
            return;
        }
        $this->continueHashChecks(false);
    }

    protected function continueHashChecks(bool $skip_reseller): void
    {
        $headers = getallheaders();
        if ($headers["HTTP_X_SECONDLIFE_SHARD"] != "Production") {
            $this->load_ok = false;
            $this->failed("wrong grid connected");
            return;
        }
        $avatar_helper = new AvatarHelper();
        $get_av_status = $avatar_helper->loadOrCreate($this->ownerkey, $this->ownername);
        if ($get_av_status == false) {
            $this->load_ok = false;
            $this->failed("Unable to load owner avatar for this object!");
            return;
        }
        $this->Object_OwnerAvatar = $avatar_helper->getAvatar();
        $region_helper = new RegionHelper();
        $get_region_status = $region_helper->loadOrCreate($this->regionname);
        if ($get_region_status == false) {
            $this->load_ok = false;
            $this->failed("Unable to load region");
            return;
        }
        $this->region = $region_helper->getRegion();
        if ($this->trackObject == true) {
            $reseller_helper = new ResellerHelper();
            $get_reseller_status = $reseller_helper->loadOrCreate(
                $this->Object_OwnerAvatar->getId(),
                $this->siteConfig->getSlConfig()->getNewResellers(),
                $this->siteConfig->getSlConfig()->getNewResellersRate()
            );
            if ($get_reseller_status == false) {
                $this->load_ok = false;
                $this->failed("Unable to load reseller");
                return;
            }
            if ($skip_reseller == false) {
                $this->reseller = $reseller_helper->getReseller();
                if ($this->siteConfig->getSlConfig()->getOwnerAvatarLink() == $this->Object_OwnerAvatar->getId()) {
                    $this->owner_override = true;
                }
                if (($this->reseller->getAllowed() == false) && ($this->owner_override == false)) {
                    $this->load_ok = false;
                    $this->failed("Unable to access this api - please contact owner");
                    return;
                }
            }
            $object_helper = new ObjectHelper();
            $get_object_status = $object_helper->loadOrCreate(
                $this->Object_OwnerAvatar->getId(),
                $this->region->getId(),
                $this->objectuuid,
                $this->objectname,
                $this->objecttype,
                $this->pos,
                true
            );
            if ($get_object_status == false) {
                $this->load_ok = false;
                $this->failed("Unable to attach object: " . $object_helper->getLastWhyFailed());
                return;
            }
            $this->object = $object_helper->getObject();
        }
    }

    protected function timeWindow(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $timewindow = 120;
        $now = time();
        if ($this->unixtime > $now) {
            if ($this->unixtime > ($now + $timewindow)) {
                $this->load_ok = false;
            }
        } elseif ($this->unixtime < $now) {
            if ($this->unixtime < ($now - $timewindow)) {
                $this->load_ok = false;
            }
        }
        if ($this->load_ok == false) {
            $this->setSwapTag("status", false);
            $this->failed("timewindow is out of scope");
            return;
        }
    }

    protected function hasAccessOwner(): bool
    {
        if ($this->owner_override == false) {
            $this->failed("SystemAPI access only - please contact support");
            return false;
        }
        return true;
    }
}
