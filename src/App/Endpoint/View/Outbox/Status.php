<?php

namespace App\Endpoint\View\Outbox;

class Status extends View
{
    /**
     * @return string[]
     *  */
    public function getPages(): array
    {
        return $this->pages;
    }
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Status");
        $services = [
        "Notecard" => ["norm" => 20,"cron" => 8,"classname" => "App\Models\Sets\NotecardSet"],
        "Docs" => ["norm" => 20,"cron" => 20,"classname" => "App\Models\Sets\NotecardmailSet"],
        "Details" => ["norm" => 15,"cron" => 8,"classname" => "App\Models\Sets\DetailSet"],
        "Mail" => ["norm" => 15,"cron" => 15,"classname" => "App\Models\Sets\MessageSet"],
        "Events" => ["norm" => 30,"cron" => 30,"classname" => "App\Models\Sets\EventsqSet"],
        "Bot" => ["norm" => 30,"cron" => 8,"classname" => "App\Models\Sets\BotcommandqSet"],
        ];
        $table_head = ["Outbox name","Pending","TTC","Cron TTC"];
        $table_body = [];
        foreach ($services as $service_name => $config) {
            $entry = [];

            $object_set = new $config["classname"]();
            $count = $object_set->countInDB();
            $countText = $count->items;
            if ($count->status == false) {
                $count = 0;
                $countText = "";
            }
            $entry[] = '<a href="[[SITE_URL]]outbox/' . $service_name . '">' . $service_name . '</a>';
            $entry[] = $countText;
            $ttc_cron = "";
            $ttc_norm = "";
            if ($count->items > 0) {
                $normTime = ($config["norm"] * $count->items);
                $ttc_norm = $this->timeRemainingHumanReadable(time() + $normTime, true, "");
                $cronTime = ($config["cron"] * $count->items);
                if ($cronTime != $normTime) {
                    $ttc_cron .= "<strong class=\"text-success\">"
                    . $this->timeRemainingHumanReadable(time() + $cronTime, true, "") . "</strong>";
                }
            }
            $entry[] = $ttc_norm;
            $entry[] = $ttc_cron;
            $table_body[] = $entry;
        }
        $this->pages["Status"] = "" . $this->renderTable($table_head, $table_body) . "<br><hr/><p>TTC is the Expected "
        . "time to clear<br/> this is if the SL service object is running normaly</p>";
    }
}
