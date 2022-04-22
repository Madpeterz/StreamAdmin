<?php

namespace App\Endpoint\View\Outbox;

class Status extends View
{
    public function process(): void
    {
        global $pages;
        $this->output->addSwapTagString("page_title", " Status");
        $services = [
        "Notecard" => ["norm" => 20,"cron" => 8,"classname" => "App\Models\Sets\NotecardSet"],
        "Docs" => ["norm" => 20,"cron" => 20,"classname" => "App\Models\Sets\NotecardmailSet"],
        "Details" => ["norm" => 15,"cron" => 8,"classname" => "App\Models\Sets\DetailSet"],
        "Mail" => ["norm" => 15,"cron" => 15,"classname" => "App\Models\Sets\MessageSet"],
        "Api" => ["norm" => 25,"cron" => 8,"classname" => "App\Models\Sets\ApirequestsSet"],
        "Events" => ["norm" => 30,"cron" => 30,"classname" => "App\Models\Sets\EventsqSet"],
        "Bot" => ["norm" => 30,"cron" => 8,"classname" => "App\Models\Sets\BotcommandqSet"],
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
            $entry[] = '<a href="[[SITE_URL]]outbox/' . $service_name . '">' . $service_name . '</a>';
            $entry[] = $countText;
            $ttc_cron = "";
            $ttc_norm = "";
            if ($count > 0) {
                $normTime = ($config["norm"] * $count);
                $ttc_norm = $this->timeRemainingHumanReadable(time() + $normTime, true, "");
                $cronTime = ($config["cron"] * $count);
                if ($cronTime != $normTime) {
                    $ttc_cron .= "<strong class=\"text-success\">"
                    . $this->timeRemainingHumanReadable(time() + $cronTime, true, "") . "</strong>";
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
