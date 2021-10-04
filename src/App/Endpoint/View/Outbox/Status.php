<?php

namespace App\Endpoint\View\Outbox;

use App\R7\Set\EventsqSet;

class Status extends View
{
    public function process(): void
    {
        global $pages;
        $this->output->addSwapTagString("page_title", " Status");
        $services = [
        "Notecard" => ["norm" => 20,"cron" => 8,"classname" => "App\R7\Set\NotecardSet"],
        "Docs" => ["norm" => 20,"cron" => 20,"classname" => "App\R7\Set\NotecardmailSet"],
        "Details" => ["norm" => 15,"cron" => 8,"classname" => "App\R7\Set\DetailSet"],
        "Mail" => ["norm" => 15,"cron" => 15,"classname" => "App\R7\Set\MessageSet"],
        "Api" => ["norm" => 25,"cron" => 8,"classname" => "App\R7\Set\ApirequestsSet"],
        "Events" => ["norm" => 30,"cron" => 30,"classname" => "App\R7\Set\EventsqSet"],
        "Bot" => ["norm" => 30,"cron" => 8,"classname" => "App\R7\Set\BotcommandqSet"],
        ];
        $table_head = ["Outbox name","Pending","TTC","Cron TTC"];
        $table_body = [];
        foreach ($services as $service_name => $config) {
            $entry = [];

            $object_set = new $config["classname"]();
            $count = $object_set->countInDB();
            $countText = $count;
            if ($count == null) {
                $count = 0;
                $countText = "";
            }
            $entry[] = '<a href="[[url_base]]outbox/' . $service_name . '">' . $service_name . '</a>';
            $entry[] = $countText;
            $ttc_cron = "";
            $ttc_norm = "";
            if ($count > 0) {
                $normTime = ($config["norm"] * $count);
                $ttc_norm = timeleftHoursAndDays(time() + $normTime, true, "");
                $cronTime = ($config["cron"] * $count);
                if ($cronTime != $normTime) {
                    $ttc_cron .= "<strong class=\"text-success\">"
                    . timeleftHoursAndDays(time() + $cronTime, true, "") . "</strong>";
                }
            }
            $entry[] = $ttc_norm;
            $entry[] = $ttc_cron;
            $table_body[] = $entry;
        }
        $pages["Status"] = "" . $this->renderTable($table_head, $table_body) . "<br><hr/><p>TTC is the Expected "
        . "time to clear<br/> this is if the SL service object is running normaly</p>";
    }
}
