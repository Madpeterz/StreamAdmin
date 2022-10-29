<?php

namespace App\Endpoint\CronJob\Master;

use App\Helpers\ObjectHelper;
use App\Helpers\RegionHelper;
use App\Models\Avatar;
use App\Models\Objects;
use App\Template\CronAjax;

abstract class Master extends CronAjax
{
    protected string $cronName = "";
    protected int $cronID = 0;
    protected string $cronRunClass = "";
    protected int $groups = 18;
    protected ?int $lockMaxGroups = null;

    protected ?Objects $myObject = null;

    protected int $ticks = 0;
    protected int $sleepTime = 0;
    protected float $avgSleep = 0;
    protected array $tickOffsets = [];

    protected function report(): void
    {
        $this->setSwapTag("task", $this->cronName);
        $this->setSwapTag("ticks", $this->ticks);
        $this->setSwapTag("sleep", $this->sleepTime);
        $this->setSwapTag("avgSleepPerTick", $this->avgSleep);
        $this->setSwapTag("offsets", json_encode($this->tickOffsets));
    }

    protected function splitLooper(): bool
    {
        $autoExitTime = time() + 55;
        $spacer = 55 / $this->groups;
        $exitNow = false;
        $hadError = false;
        $nextDelay = 0;
        $sleeps = 0;
        $totalsleep = 0;
        $startUnixTime = time();
        while ($exitNow == false) {
            if ($nextDelay > 0) {
                $sleeps++;
                $totalsleep += $nextDelay;
                $this->sleepTime += $nextDelay;
                sleep(round($nextDelay));
            }
            $dif = time() - $startUnixTime;
            $this->tickOffsets[] = $dif;
            if (($this->ticks % 3) == 0) {
                $this->save($hadError); // auto save
            }
            $startTaskTime = time();
            $hadError = !$this->doTask();
            $this->ticks++;
            if ($hadError == true) {
                $exitNow = true;
            }
            $timeTaken = time() - $startTaskTime;
            if ((time() + $timeTaken) > $autoExitTime) {
                $exitNow = true;
            }
            $nextDelay = $spacer - $timeTaken;
            if ($nextDelay < 1) {
                $nextDelay = 0;
            }
            if ($this->ticks >= $this->groups) {
                $exitNow = true;
            }
        }
        if ($sleeps > 0) {
            $this->avgSleep = round($totalsleep / $sleeps, 2);
        }
        return $hadError;
    }
    protected function doTask(): bool
    {
        $task = new $this->cronRunClass();
        $task->process();
        return $task->getOutputObject()->getSwapTagBool("status");
    }
    protected function save(bool $hadError = false): void
    {
        global $system, $cache;
        if (($hadError == false) && ($this->myObject != null)) {
            $this->myObject->setLastSeen(time());
            $updateObj = $this->myObject->updateEntry();
            $hadError = !$updateObj->status;
            if ($hadError == true) {
                echo "task: " . $this->cronName . " - Failed to up object last seen timer\n";
            }
        }
        if ($hadError == false) {
            if ($cache != null) {
                $cache->shutdown(); // push changes to cache now.
            }
            $system->getSQL()->sqlSave(false); // push changes to DB now.
            return;
        }
        if ($cache != null) {
            $cache->purge(); // something is wrong purge the cache.
            $system->getSQL()->sqlRollBack(); // something is wrong undo any changes.
        }
    }
    protected function startup(): bool
    {
        global $system;
        if ($this->cronID != 1) {
            // delay startup by 2 sec so regions can be created
            sleep(2);
        }
        $regionHelper = new RegionHelper();
        if ($regionHelper->loadOrCreate("cronJob") == false) {
            echo "task: " . $this->cronName . " - Unable to load/create region:" . $regionHelper->getLastError();
            return false;
        }
        if ($this->cronID == 1) {
            $this->save(); // force region creation.
        }
        $region = $regionHelper->getRegion();

        if ($system->getSlConfig()->isLoaded() == false) {
            echo "task: " . $this->cronName . " - Unable to load system config:"
            . $system->getSlConfig()->getLastErrorBasic();
            return false;
        }

        $avatar = new Avatar();
        if ($avatar->loadID($system->getSlConfig()->getOwnerAvatarLink())->status == false) {
            echo "task: " . $this->cronName . " - Unable to load owner avatar:" . $avatar->getLastErrorBasic();
            return false;
        }

        $objectHelper = new ObjectHelper();
        if (
            $objectHelper->loadOrCreate(
                $avatar->getId(),
                $region->getId(),
                "00000000-0000-0000-0000-00000000000" . $this->cronID,
                $this->cronName,
                $this->cronName,
                "0,0,0"
            ) == false
        ) {
            echo "task: " . $this->cronName . " - Unable to load/create object:" . $objectHelper->getLastWhyFailed();
            return false;
        }
        $this->save(); // force save the object
        $this->myObject = $objectHelper->getObject();

        return true;
    }
    public function process(): void
    {
        $this->groups = 15;
        if (defined("TESTING") == true) {
            $this->groups = 1;
        }
        if ($this->lockMaxGroups != null) {
            if ($this->groups > $this->lockMaxGroups) {
                $this->groups = $this->lockMaxGroups;
            }
        }
        if ($this->startup() == false) {
            return;
        }
        $this->save();
        $hadError = $this->splitLooper();
        if ($hadError == true) {
            return;
        }
        $this->save();
        $this->report();
    }
}
