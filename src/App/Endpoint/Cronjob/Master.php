<?php

namespace App\Endpoint\Cronjob;

use App\Helpers\ObjectHelper;
use App\Helpers\RegionHelper;
use App\Models\Avatar;
use App\Models\Region;
use App\Template\ControlAjax;
use App\Template\SecondlifeAjax;

abstract class Master extends ControlAjax
{
    protected ?SecondlifeAjax $taskClass = null;
    protected string $objectType = "";
    protected string $taskNicename = "";
    protected int $taskId = 0;
    protected bool $createRegion = false;

    protected Region $region;

    protected int $taskTime = 55;
    protected int $ticks = 0;
    protected array $tickTimes = [];
    protected int $startUnix = 0;
    protected int $endUnix = 0;
    protected int $sleepTime = 0;
    protected int $sleeps = 0;
    protected bool $fastExit = false;

    public function process(): void
    {
        $this->startUnix = time();
        if ($this->taskClass === null) {
            return;
        }
        $this->loadRegion();
        if ($this->loadObject() == false) {
            $this->failed("Unable to create cron object");
            return;
        }
        $this->taskClass->setOwnerOverride(true);
        $this->taskClass->setRegion($this->region);
        $this->cronLoop();
        $this->report();
    }

    protected function report(): void
    {
        $this->setSwapTag("ticks", $this->ticks);
        $this->setSwapTag("tickTimes", implode(",", $this->tickTimes));
        $this->setSwapTag("sleeps", $this->sleeps);
        $this->setSwapTag("sleepTime", $this->sleepTime);
        $this->setSwapTag("sleepAvg", 0);
        if ($this->sleeps > 0) {
            $this->setSwapTag("sleepAvg", round($this->sleepTime / $this->sleeps, 2));
        }
        $this->setSwapTag("start", $this->startUnix);
        $this->setSwapTag("end", time());
        $this->setSwapTag("unused", $this->taskTime);
        $this->setSwapTag("fastExit", $this->fastExit);
    }

    protected function doTask(): bool
    {
        $this->taskClass->process();
        return true;
    }

    protected function cronLoop(): void
    {
        $exit = false;

        while (($exit == false) && ($this->fastExit == false)) {
            $startLoop = time();
            $this->ticks++;
            $this->tickTimes[] = ($startLoop - $this->startUnix);
            if ($this->doTask() == false) {
                $this->fastExit = true;
            }
            $this->output = $this->taskClass->getOutputObject();
            $dif = time() - $startLoop;
            $sleepTime = 2 - $dif;
            if ($dif < 0) {
                $sleepTime = 0;
            }
            $this->taskTime -= $dif;
            $this->taskTime -= $sleepTime;
            if ($this->output->getSwapTagBool("status") == false) {
                $this->fastExit = true;
                break;
            }
            if ($this->taskTime < 5) {
                $exit = true;
            }
            if (defined("UNITTEST") == true) {
                $this->fastExit = true;
                $sleepTime = 0;
                break;
            }
            if (($sleepTime > 0) && ($exit == false)) {
                sleep($sleepTime);
                $this->sleeps++;
                $this->sleepTime += $sleepTime;
            }
        }
    }

    protected function save(): bool
    {
        return $this->siteConfig->getSQL()->sqlSave(false);
    }

    protected function loadObject(): bool
    {
        $avatar = new Avatar();
        if ($avatar->loadID($this->siteConfig->getSlConfig()->getOwnerAvatarLink())->status == false) {
            $this->addError(
                "task: " . $this->taskNicename . " - Unable to load/create avatar:" . $avatar->getLastError()
            );
            return false;
        }
        $objectHelper = new ObjectHelper();
        if (
            $objectHelper->loadOrCreate(
                $avatar->getId(),
                $this->region->getId(),
                "00000000-0000-0000-0000-00000000000" . $this->taskId,
                $this->taskNicename,
                $this->objectType,
                "0,0,0"
            ) == false
        ) {
            $this->addError(
                "task: " . $this->taskNicename . " - Unable to load/create object:" . $objectHelper->getLastWhyFailed()
            );
            return false;
        }
        return $this->save();
    }

    protected function loadRegion(): bool
    {
        if ($this->createRegion == false) {
            $this->taskTime -= 2;
            sleep(2);
        }
        $regionHelper = new RegionHelper();
        if ($regionHelper->loadOrCreate("cron") == false) {
            return false;
        }
        $this->region = $regionHelper->getRegion();
        return true;
    }
}
