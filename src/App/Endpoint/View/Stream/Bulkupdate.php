<?php

namespace App\Endpoint\View\Stream;

use App\Models\Sets\ServerSet;
use App\Models\Sets\StreamSet;
use YAPF\Bootstrap\Template\Form;

class Bulkupdate extends View
{
    public function process(): void
    {
        $this->setSwapTag("html_title", "Streams");
        $this->output->addSwapTagString("page_title", " Bulk update");
        $this->setSwapTag("page_actions", "");
        $whereconfig = [
        "fields" => ["needWork","rentalLink"],
        "values" => [1,null],
        "types" => ["i","i"],
        "matches" => ["=","IS"],
        ];
        $stream_set = new StreamSet();
        $stream_set->loadWithConfig($whereconfig);
        if ($stream_set->getCount() == 0) {
            $this->setSwapTag("page_content", "No streams marked as need work");
            return;
        }

        $server_set = new ServerSet();
        $server_set->loadAll();

        $table_head = ["Action","Server","Port","Encoder/Stream password","Admin Password"];
        $table_body = [];

        foreach ($stream_set as $stream) {
            $server = $server_set->getObjectByID($stream->getServerLink());
            $action = '
<div class="btn-group btn-group-toggle" data-toggle="buttons">
<label class="btn btn-outline-success">
<input type="radio" value="update" name="stream' . $stream->getStreamUid() . '" autocomplete="off"> Update
</label>
<label class="btn btn-outline-secondary active">
<input type="radio" value="skip" name="stream' . $stream->getStreamUid() . '" autocomplete="off" checked> Skip
</label>
</div>';
            $entry = [];
            //$entry[] = $stream->getId();
            $entry[] = $action;
            $entry[] = $server->getDomain();
            $entry[] = $stream->getPort();
            $adminPassword = '<input type="text" class="form-control" name="stream' . $stream->getStreamUid()
            . 'adminpw" value="' . $stream->getAdminPassword() . '" placeholder="Max 20 length">';
            $djPassword = '<input type="text" class="form-control" name="stream'
            . $stream->getStreamUid() . 'djpw" value="' . $stream->getDjPassword()
            . '" placeholder="Max 20 length">';
            $entry[] = $djPassword;
            $entry[] = $adminPassword;
            $table_body[] = $entry;
        }

        if (count($table_body) == 0) {
            $this->setSwapTag("page_content", "No streams need updates!");
            return;
        }
        $form = new Form();
        $form->target("stream/bulkupdate");
        $form->col(12);
        $form->directAdd($this->renderTable($table_head, $table_body));
        $form->directAdd("<a href=\"#\" class=\"bulkactiontoggle\">Toggle</a>");
        $this->setSwapTag("page_content", $form->render("Process", "outline-warning"));
    }
}
