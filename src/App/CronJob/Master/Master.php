<?php

namespace App\CronJob\Master;

use App\Helpers\ObjectHelper;
use App\Helpers\RegionHelper;
use App\R7\Model\Avatar;
use App\R7\Model\Objects;
use App\R7\Model\Slconfig;

abstract class Master
{
    protected string $cronName = "";
    protected int $cronID = 0;
    protected string $cronRunClass = "";
    protected int $groups = 18;
    protected ?int $lockMaxGroups = null;

    protected ?Objects $myObject = null;

    protected int $ticks = 0;
    protected int $sleepTime = 0;
    protected float $startMicrotime = 0;
    protected float $endMicrotime = 0;
    protected float $avgSleep = 0;
    protected array $tickOffsets = [];

    public function __construct(int $forceSetGroups = 15)
    {
        $this->groups = $forceSetGroups;
        if ($this->lockMaxGroups != null) {
            if ($this->groups > $this->lockMaxGroups) {
                $this->groups = $this->lockMaxGroups;
            }
        }
        $this->process();
    }

    protected function report(): void
    {
        $output = [
            "task" => $this->cronName,
            "ticks" => $this->ticks,
            "sleep" => $this->sleepTime,
            "startTime" => date("H:i:s", $this->startMicrotime),
            "endTime" => date("H:i:s", $this->endMicrotime),
            "totalTime" => date("i:s", $this->endMicrotime - $this->startMicrotime),
            "avgSleepPerTick" => $this->avgSleep,
            "offsets" => json_encode($this->tickOffsets),
        ];
        echo json_encode($output) . "\n";
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
                sleep($nextDelay);
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
        $task->setOwnerOverride(true);
        $task->process();
        return $task->getOutputObject()->getSwapTagBool("status");
    }
    protected function save(bool $hadError = false): void
    {
        global $sql, $cache;
        if (($hadError == false) && ($this->myObject != null)) {
            $this->myObject->setLastSeen(time());
            $updateObj = $this->myObject->updateEntry();
            $hadError = !$updateObj["status"];
            if ($hadError == true) {
                echo "task: " . $this->cronName . " - Failed to up object last seen timer\n";
            }
        }
        if ($hadError == false) {
            if ($cache != null) {
                $cache->shutdown(); // push changes to cache now.
            }
            $sql->sqlSave(false); // push changes to DB now.
            return;
        }
        if ($cache != null) {
            $cache->purge(); // something is wrong purge the cache.
            $sql->sqlRollBack(); // something is wrong undo any changes.
        }
    }
    protected function startup(): bool
    {
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

        $slconfig = new Slconfig();
        if ($slconfig->loadID(1) == false) {
            echo "task: " . $this->cronName . " - Unable to load system config:" . $slconfig->getLastErrorBasic();
            return false;
        }

        $avatar = new Avatar();
        if ($avatar->loadID($slconfig->getOwnerAvatarLink()) == false) {
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
    protected function process(): void
    {
        $this->startMicrotime = microtime(true);
        if ($this->startup() == false) {
            return;
        }
        $this->save();
        $hadError = $this->splitLooper();
        if ($hadError == true) {
            return;
        }
        $this->endMicrotime = microtime(true);
        $this->save();
        $this->report();
    }
}
