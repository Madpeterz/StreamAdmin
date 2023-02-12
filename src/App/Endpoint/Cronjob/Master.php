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
    protected SecondlifeAjax $taskClass;
    protected string $taskNicename = "";
    protected string $objectType = "";
    protected int $autoExitTime = 0;
    protected int $taskId = 0;

    // stats
    protected int $ticks = 0;
    protected int $sleepTime = 0;
    protected array $tickOffsets = [];
    protected int $startUnixtime = 0;
    protected Region $region;

    protected function reportCard(): void
    {
        $this->ok("cronFinished");
        $this->setSwapTag("ticks", $this->ticks);
        $this->setSwapTag("totalSleep", $this->sleepTime);
        $this->setSwapTag("sleepAvg", round($this->sleepTime / $this->ticks));
        $this->setSwapTag("tickOffsets", $this->tickOffsets);
        $this->setSwapTag("startUnixtime", $this->startUnixtime);
        $this->setSwapTag("endUnixtime", time());
    }
    protected function doTask(): bool
    {
        $this->taskClass->process();
        $reply = $this->taskClass->getOutputObject();
        if ($reply->getSwapTagBool("status") == false) {
            $this->failed($reply->getSwapTagString("message"));
            return false;
        }
        return true;
    }
    protected function cronRegion(): bool
    {
        if ($this->create == false) {
            sleep(2); // force a small delay to allow regions to be setup (cron load ordering)
        }
        $regionHelper = new RegionHelper();
        if ($regionHelper->loadOrCreate("cronJob") == false) {
            $this->addError(
                "task: " . $this->taskNicename . " - Unable to load/create region:" . $regionHelper->getLastError()
            );
            return false;
        }
        if ($this->create == true) {
            $this->save();
        }
        $this->region = $regionHelper->getRegion();
        return true;
    }

    protected function cronObject(): bool
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
        $this->save(); // update the ping timer
        return true;
    }

    public function save(): void
    {
        // force save changes now
        if ($this->siteConfig->getSQL()->sqlSave(false) == false) {
            die("Failed to save changes to DB " . $this->siteConfig->getSQL()->getLastErrorBasic());
        }
        if ($this->siteConfig->getCacheEnabled() == false) {
            return;
        }
        if ($this->siteConfig->getCacheWorker()->save() == false) {
            die("Failed to save changes to Cache " . $this->siteConfig->getCacheWorker()->getLastErrorBasic());
        }
    }

    public function process(): void
    {
        $this->startUnixtime = time();
        $this->autoExitTime = $this->startUnixtime + 56;
        if ($this->objectType == "") {
            $this->failed("Unknown cronjob task selected");
            return;
        }
        $this->cronRegion();
        $this->taskLoop();
    }
    protected bool $create = false;
    protected int $groups = 12;

    protected function taskLoop(): void
    {
        $loopFailed = false;
        $exit = false;
        if (defined("TESTING") == true) {
            $groups = 1;
        }

        while (($loopFailed == false) && ($exit == false)) {
            $startLoopTime = time();
            $this->tickOffsets[] = $startLoopTime;
            if ($this->cronObject() == false) {
                $loopFailed = true;
                break;
            }
            if ($this->doTask() == false) {
                $loopFailed = true;
                break;
            }
            if ($this->cronObject() == false) {
                $loopFailed = true;
                break;
            }
            $endLoopTime = time();
            $dif = floor($endLoopTime - $startLoopTime);
            $sleepfor = 0;
            if ($dif < 5) {
                $sleepfor = 5 - $dif;
                $this->sleepTime += $sleepfor;
                $dif = 5;
            }
            $this->ticks++;
            if ((time() + $dif) > $this->autoExitTime) {
                $exit = true; // next loop will not finish in time
            }
            if ($this->ticks >= $groups) {
                $exit = true; // we are done for this set of cron activations
            }
            if ($sleepfor > 0) {
                sleep($sleepfor); // loop is to fast, wait upto 5 secs before continue
            }
        }
        if ($loopFailed == false) {
            $this->reportCard();
        }
    }
}
