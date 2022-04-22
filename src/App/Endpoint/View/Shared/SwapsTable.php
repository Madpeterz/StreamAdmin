<?php

namespace App\Endpoint\View\Shared;

use App\Framework\Menu;

abstract class SwapsTable extends Menu
{
    protected bool $use_paged_swaps = false;
    protected array $pages = [];
    public function getSwaps(): void
    {
        $example_time = time();
        $example_time += $this->siteConfig->unixtimeWeek() + rand(1000, 5000);
        $swaps = [
            "AVATAR_FIRSTNAME" => "Madpeter",
            "AVATAR_LASTNAME" => "Zond",
            "AVATAR_FULLNAME" => "Madpeter Zond",
            "RENTAL_EXPIRES_DATETIME" => date('l jS \of F Y h:i:s A', $example_time),
            "RENTAL_TIMELEFT" => $this->timeRemainingHumanReadable($example_time),
            "STREAM_PORT" => 4000,
            "STREAM_ADMINUSERNAME" => "SuperAdmin",
            "STREAM_ADMINPASSWORD" => "AdminPaSSwordHere",
            "STREAM_DJPASSWORD" => "DJpasswordYo",
            "STREAM_MOUNTPOINT" => "/live",
            "SERVER_DOMAIN" => "livestreamservice.demo OR ip address",
            "SERVER_CONTROLPANEL" => "https://livestreamservice.demo:5000",
            "PACKAGE_NAME" => "CheapWeeklyPackage",
            "PACKAGE_LISTENERS" => 10,
            "PACKAGE_BITRATE" => 128,
            "PACKAGE_AUTODJ" => "Enabled",
            "PACKAGE_AUTODJ_SIZE" => 3,
            "PACKAGE_UID" => "XXXXXXXX",
            "RENTAL_UID" => "XXXXXXXX",
            "NL" => "~ Creates a new line ~",
            "TIMEZONE" => "Europe / London",
            "FirstName" => "Madpeter",
            "LastName" => "Zond",
            "ExpiresAt" => date('l jS \of F Y h:i:s A', $example_time),
            "TimeLeft" => $this->timeRemainingHumanReadable($example_time),
            "PortNum" => 4000,
            "AdminUsername" => "SuperAdmin",
            "AdminPassword" => "AdminPaSSwordHere",
            "DjPassword" => "DJpasswordYo",
            "MountPoint" => "/live",
            "Package" => "CheapWeeklyPackage",
            "Users" => 10,
            "Kbps" => 128,
            "AutoDJ" => "Enabled",
            "Disk" => 3,
            "PackUID" => "XXXXXXXX",
            "RentUID" => "XXXXXXXX",
        ];

        $table_head = ["Tag","Example","Tag","Example"];
        $table_body = [];
        $loop = 0;
        $current = [];
        foreach ($swaps as $key => $value) {
            $current[] = "[[" . $key . "]]";
            $current[] = $value;
            if (count($current) == 4) {
                $table_body[] = $current;
                $current = [];
            }
            $loop++;
        }
        if (count($current) != 0) {
            $current[] = " ";
            $current[] = " ";
            $table_body[] = $current;
        }
        $tableout = $this->renderTable($table_head, $table_body);
        if ($this->use_paged_swaps == true) {
            $this->pages["Swaps"] = $tableout;
            return;
        }
        $this->output->addSwapTagString("page_content", "<br/><hr/><br/>" . $tableout);
    }
}
