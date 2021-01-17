<?php

namespace App\Template;

use App\Models\Avatar;
use App\Models\Region;
use App\Models\Reseller;
use avatar_helper;
use object_helper;
use region_helper;
use reseller_helper;
use YAPF\InputFilter\InputFilter;

abstract class SecondlifeAjax extends View
{
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

    protected ?Avatar $object_owner_avatar;
    protected ?Region $region;
    protected ?Reseller $reseller;
    protected bool $owner_override = false;
    protected ?Object $object;
    protected bool $soft_fail = false;

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
            $this->setSwapTag("status", "false");
            return;
        }
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
            "region",
            "ownerkey",
            "ownername",
            "pos",
            "objectname",
            "objecttype",
        ];
        $lookups = [
            "region" => "regionname",
        ];
        $input = new InputFilter();
        $this->staticpart = "";
        foreach ($required_sl as $slvalue) {
            $keeplookup = $slvalue;
            if (array_key_exists($slvalue, $lookups) == true) {
                $slvalue = $lookups[$slvalue];
            }
            $value = $input->postFilter($keeplookup);
            if ($value !== null) {
                $this->$$slvalue = $value;
                $this->staticpart .= $value;
            } else {
                $this->load_ok = false;
            }
        }
        $this->unixtime = $input->postFilter("unixtime");
        if ($this->unixtime === null) {
            $this->load_ok = false;
        }
        $hash = $input->postFilter("hash");
        if ($hash === null) {
            $this->load_ok = false;
        }
        if ($this->load_ok == false) {
            $this->setSwapTag("message", "One or more required values are missing");
            return;
        }
    }

    protected function hashCheck(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $hashcheck = sha1($this->unixtime . "" . $this->staticpart . "" . $this->slconfig->getSllinkcode());
        if ($hashcheck != $this->hash) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to vaildate request to API endpoint");
            return;
        }
        $avatar_helper = new avatar_helper();
        $get_av_status = $avatar_helper->load_or_create($this->ownerkey, $this->ownername);
        if ($get_av_status == false) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to load owner avatar for this object!");
            return;
        }
        $this->object_owner_avatar = $avatar_helper->get_avatar();
        $region_helper = new region_helper();
        $get_region_status = $region_helper->load_or_create($this->regionname);
        if ($get_region_status == false) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to load region");
            return;
        }
        $this->region = $region_helper->get_region();
        $reseller_helper = new reseller_helper();
        $get_reseller_status = $reseller_helper->load_or_create(
            $this->object_owner_avatar->getId(),
            $this->slconfig->getNew_resellers(),
            $this->slconfig->getNew_resellers_rate()
        );
        if ($get_reseller_status == false) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to load reseller");
            return;
        }
        $this->reseller = $reseller_helper->get_reseller();
        if ($this->slconfig->getOwner_av() == $this->object_owner_avatar->getId()) {
            $this->owner_override = true;
        }
        if (($this->reseller->getAllowed() == false) && ($this->owner_override == false)) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to access this api - please contact owner");
            return;
        }
        $object_helper = new object_helper();
        $get_object_status = $object_helper->load_or_create(
            $this->object_owner_avatar->getId(),
            $this->region->getId(),
            $this->objectuuid,
            $this->objectname,
            $this->objecttype,
            $this->pos,
            true
        );
        if ($get_object_status == false) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to attach object");
            return;
        }
        $this->object = $object_helper->get_object();
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
            $this->setSwapTag("status", "false");
            $this->setSwapTag("message", "timewindow is out of scope");
            return;
        }
    }

    public function renderPage(): void
    {
        $this->output->renderSecondlifeAjax();
    }
}
