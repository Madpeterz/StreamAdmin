<?php

namespace App\Template;

use App\Helpers\AvatarHelper;
use App\Helpers\ObjectHelper;
use App\Helpers\RegionHelper;
use App\Helpers\ResellerHelper;
use App\R7\Model\Avatar;
use App\R7\Model\Region;
use App\R7\Model\Reseller;
use YAPF\InputFilter\InputFilter;

abstract class SecondlifeAjax extends View
{
    protected $method = "";
    protected $action = "";
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
    protected ?Object $object;
    protected bool $soft_fail = false;

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

    public function __construct(bool $AutoLoadTemplate = false)
    {
        parent::__construct($AutoLoadTemplate);
        $this->requiredValues();
        $this->timeWindow();
        $this->hashCheck();
        if ($this->load_ok == false) {
            $this->setSwapTag("status", false);
            return;
        }
        $this->setSwapTag("message", "ready");
        $this->output->tempateSecondLifeAjax();
    }

    protected function requiredValues(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $required_sl = [
            "method",
            "action",
            "mode",
            "objectuuid",
            "regionname",
            "ownerkey",
            "ownername",
            "pos",
            "objectname",
            "objecttype",
        ];

        $input = new InputFilter();
        $this->staticpart = "";
        foreach ($required_sl as $slvalue) {
            $value = $input->postFilter($slvalue);
            if ($value !== null) {
                $this->$slvalue = $value;
                $this->staticpart .= $value;
            } else {
                $this->load_ok = false;
                $this->setSwapTag("message", "Value: " . $slvalue . " is missing");
                return;
            }
        }
        $this->unixtime = $input->postFilter("unixtime");
        if ($this->unixtime === null) {
            $this->setSwapTag("message", "Missing unixtime value");
            $this->load_ok = false;
            return;
        }
        $this->hash = $input->postFilter("hash");
        if ($this->hash === null) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Missing hash value");
            return;
        }
    }

    protected function hashCheck(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $raw = $this->unixtime . "" . $this->staticpart . "" . $this->slconfig->getSlLinkCode();
        $hashcheck = sha1($raw);
        if ($hashcheck != $this->hash) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to vaildate request to API endpoint: ");
            return;
        }
        $this->continueHashChecks(false);
    }

    protected function continueHashChecks(bool $skip_reseller): void
    {
        $avatar_helper = new AvatarHelper();
        $get_av_status = $avatar_helper->loadOrCreate($this->ownerkey, $this->ownername);
        if ($get_av_status == false) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to load owner avatar for this object!");
            return;
        }
        $this->Object_OwnerAvatar = $avatar_helper->getAvatar();
        $region_helper = new RegionHelper();
        $get_region_status = $region_helper->loadOrCreate($this->regionname);
        if ($get_region_status == false) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to load region");
            return;
        }
        $this->region = $region_helper->getRegion();
        $reseller_helper = new ResellerHelper();
        $get_reseller_status = $reseller_helper->loadOrCreate(
            $this->Object_OwnerAvatar->getId(),
            $this->slconfig->getNewResellers(),
            $this->slconfig->getNewResellersRate()
        );
        if ($get_reseller_status == false) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to load reseller");
            return;
        }
        if ($skip_reseller == false) {
            $this->reseller = $reseller_helper->getReseller();
            if ($this->slconfig->getOwnerAvatarLink() == $this->Object_OwnerAvatar->getId()) {
                $this->owner_override = true;
            }
            if (($this->reseller->getAllowed() == false) && ($this->owner_override == false)) {
                $this->load_ok = false;
                $this->setSwapTag("message", "Unable to access this api - please contact owner");
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
            $this->setSwapTag("message", "Unable to attach object: " . $object_helper->getLastWhyFailed());
            return;
        }
        $this->object = $object_helper->getObject();
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
            $this->setSwapTag("message", "timewindow is out of scope");
            return;
        }
    }

    public function renderPage(): void
    {
        $this->output->renderSecondlifeAjax();
    }
}
