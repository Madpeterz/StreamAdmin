<?php

namespace App\CronJob\Master;

use App\Helpers\ObjectHelper;
use App\Helpers\RegionHelper;
use App\R7\Model\Avatar;
use App\R7\Model\Slconfig;

abstract class Master
{
    protected string $cronName = "";
    protected int $cronID = 0;
    protected string $cronRunClass = "";
    protected int $groups = 15;
    protected ?int $lockMaxGroups = null;

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

    protected function process(): void
    {
        $regionHelper = new RegionHelper();
        $regionHelper->loadOrCreate("cronJob");

        $slconfig = new Slconfig();
        $slconfig->loadID(1);

        $avatar = new Avatar();
        $avatar->loadID($slconfig->getOwnerAvatarLink());

        $objectHelper = new ObjectHelper();
        $objectHelper->loadOrCreate(
            $avatar->getId(),
            $regionHelper->getRegion()->getId(),
            "00000000-0000-0000-0000-00000000000" . $this->cronID,
            $this->cronName,
            $this->cronName,
            "0,0,0"
        );

        $ticks = 0;
        $units = 50 / $this->groups;
        $autoExit = time() + 50;
        $cronTimeStart = time();
        $sleepFor = 0;
        $tasksFinishedOk = 0;
        $tasksFinsihedFailed = 0;
        $totalSleepTime = 0;
        while (
            ($ticks < $this->groups) &&
            (time() < $autoExit)
        ) {
            $startTime = time();
            if ($sleepFor != 0) {
                $totalSleepTime += $sleepFor;
                sleep($sleepFor);
            }
            if (($ticks % 3) == 0) {
                $obj = $objectHelper->getObject();
                $obj->setLastSeen(time());
                $obj->updateEntry();
            }

            $task = new $this->cronRunClass();
            $task->setOwnerOverride(true);
            $task->process();
            $statussql = $task->getOutputObject()->getSwapTagBool("status");
            $tasksFinsihedFailed++;
            if ($statussql == false) {
                $task->getoutput();
            }
            if ($statussql == true) {
                $tasksFinishedOk++;
                $tasksFinsihedFailed--;
            }
            global $sql;
            if (($statussql === false) || ($statussql === null)) {
                $sql->flagError();
            }
            $sql->sqlSave();

            $dif = time() - $startTime;
            $sleepFor = ceil($units - $dif);
            $ticks++;
        }
        $timeInCron = time() - $cronTimeStart;
        $reply = [
            "time" => $timeInCron,
            "ok" => $tasksFinishedOk,
            "failed" => $tasksFinsihedFailed,
            "ticks" => $tasksFinsihedFailed + $tasksFinishedOk,
            "sleeped" => $totalSleepTime,
        ];
        echo json_encode($reply);
    }
}
