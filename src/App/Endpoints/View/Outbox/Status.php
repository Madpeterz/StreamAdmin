<?php

namespace App\Endpoints\View\Outbox;

class Status extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Status");
        $services = [
        "notecard" => ["timeper" => 30,"classname" => "App\NotecardSet"],
        "details" => ["timeper" => 15,"classname" => "App\DetailSet"],
        "mail" => ["timeper" => 15,"classname" => "App\MessageSet"],
        "api" => ["timeper" => 10,"classname" => "App\ApirequestsSet"],
        ];
        $table_head = ["Outbox name","Pending","TTC"];
        $table_body = [];
        foreach ($services as $service_name => $config) {
            $entry = [];
            $entry[] = '<a href="[[url_base]]outbox/' . $service_name . '">' . $service_name . '</a>';
            $object_set = new $config["classname"]();
            $object_set->loadAll();
            $entry[] = $object_set->getCount();
            $time_to_clear = ($config["timeper"] * $object_set->getCount());
            if ($time_to_clear > 60) {
                $mins = floor($time_to_clear / 60);
                if ($mins > 60) {
                    $hours = floor($mins / 60);
                    $entry[] = $mins . " hours";
                } else {
                    $entry[] = $mins . " mins";
                }
            } else {
                $entry[] = $time_to_clear . " secs";
            }
            $table_body[] = $entry;
        }
        $pages["Status"] = "" . render_table($table_head, $table_body) . "<br><hr/><p>TTC is the Expected "
        . "time to clear<br/> this is if the SL service object is running normaly</p>";
    }
}
