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
        "Notecard" => ["timeper" => 20,"classname" => "App\R7\Set\NotecardSet"],
        "Docs" => ["timeper" => 20,"classname" => "App\R7\Set\NotecardmailSet"],
        "Details" => ["timeper" => 15,"classname" => "App\R7\Set\DetailSet"],
        "Mail" => ["timeper" => 15,"classname" => "App\R7\Set\MessageSet"],
        "Api" => ["timeper" => 10,"classname" => "App\R7\Set\ApirequestsSet"],
        "Events" => ["timeper" => 30,"classname" => "App\R7\Set\EventsqSet"],
        "Bot" => ["timeper" => 30,"classname" => "App\R7\Set\BotcommandqSet"],
        ];
        $table_head = ["Outbox name","Pending","TTC"];
        $table_body = [];
        foreach ($services as $service_name => $config) {
            $entry = [];
            $entry[] = '<a href="[[url_base]]outbox/' . $service_name . '">' . $service_name . '</a>';
            $object_set = new $config["classname"]();
            $count = $object_set->countInDB();
            if ($count == null) {
                $count = 0;
            }
            $entry[] = $count;
            $time_to_clear = ($config["timeper"] * $count);
            if ($time_to_clear > 60) {
                $mins = floor($time_to_clear / 60);
                if ($mins > 60) {
                    $hours = floor($mins / 60);
                    $entry[] = $hours . " hours";
                } else {
                    $entry[] = $mins . " mins";
                }
            } else {
                $entry[] = $time_to_clear . " secs";
            }
            $table_body[] = $entry;
        }
        $pages["Status"] = "" . $this->renderTable($table_head, $table_body) . "<br><hr/><p>TTC is the Expected "
        . "time to clear<br/> this is if the SL service object is running normaly</p>";
    }
}
