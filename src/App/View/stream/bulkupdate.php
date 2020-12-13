<?php

namespace App\View\Stream;

use App\Models\ServerSet;
use App\Models\StreamSet;
use App\Template\Form;

class Bulkupdate extends View
{
    public function process(): void
    {
        $this->output->setSwapTagString("html_title", "Streams");
        $this->output->addSwapTagString("page_title", " Bulk update");
        $this->output->setSwapTagString("page_actions", "");
        $whereconfig = [
        "fields" => ["needwork","rentallink"],
        "values" => [1,null],
        "types" => ["i","i"],
        "matches" => ["=","IS"],
        ];
        $stream_set = new StreamSet();
        $stream_set->loadWithConfig($whereconfig);
        if ($stream_set->getCount() == 0) {
            $this->output->setSwapTagString("page_content", "No streams marked as need work");
            return;
        }

        $server_set = new ServerSet();
        $server_set->loadAll();

        $table_head = ["id","Action","Server","Port","Encoder/Stream password","Admin Password"];
        $table_body = [];

        foreach ($stream_set->getAllIds() as $streamid) {
            $stream = $stream_set->getObjectByID($streamid);
            $server = $server_set->getObjectByID($stream->getServerlink());
            if ($stream->getOriginal_adminusername() == $stream->getAdminusername()) {
                $action = '
<div class="btn-group btn-group-toggle" data-toggle="buttons">
<label class="btn btn-outline-success">
<input type="radio" value="update" name="stream' . $stream->getStream_uid() . '" autocomplete="off"> Update
</label>
<label class="btn btn-outline-secondary active">
<input type="radio" value="skip" name="stream' . $stream->getStream_uid() . '" autocomplete="off" checked> Skip
</label>
</div>';
                $entry = [];
                $entry[] = $stream->getId();
                $entry[] = $action;
                $entry[] = $server->getDomain();
                $entry[] = $stream->getPort();
                $adminpassword = '<input type="text" class="form-control" name="stream' . $stream->getStream_uid()
                . 'adminpw" value="' . $stream->getAdminpassword() . '" placeholder="Max 20 length">';
                $djpassword = '<input type="text" class="form-control" name="stream'
                . $stream->getStream_uid() . 'djpw" value="' . $stream->getDjpassword()
                . '" placeholder="Max 20 length">';
                $entry[] = $adminpassword;
                $entry[] = $djpassword;
                $table_body[] = $entry;
            }
        }

        if (count($table_body) == 0) {
            $this->output->setSwapTagString("page_content", "No streams need updates!");
            return;
        }
        $form = new Form();
        $form->target("stream/bulkupdate");
        $form->col(12);
        $form->directAdd(render_datatable($table_head, $table_body));
        $this->output->setSwapTagString("page_content", $form->render("Process", "outline-warning"));
    }
}
