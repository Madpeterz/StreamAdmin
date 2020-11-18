<?php

$this->output->setSwapTagString("html_title", "Streams");
$this->output->addSwapTagString("page_title", " Bulk update");
$this->output->setSwapTagString("page_actions", "");
$whereconfig = [
    "fields" => ["needwork","rentallink"],
    "values" => [1,null],
    "types" => ["i","i"],
    "matches" => ["=","IS"],
];
$stream_set = new stream_set();
$stream_set->load_with_config($whereconfig);
$server_set = new server_set();
$server_set->loadAll();

$table_head = ["id","Action","Server","Port","Encoder/Stream password","Admin Password"];
$table_body = [];

foreach ($stream_set->getAllIds() as $streamid) {
    $stream = $stream_set->getObjectByID($streamid);
    $server = $server_set->getObjectByID($stream->get_serverlink());
    if ($stream->get_original_adminusername() == $stream->get_adminusername()) {
        $action = '
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
          <label class="btn btn-outline-success">
            <input type="radio" value="update" name="stream' . $stream->get_stream_uid() . '" autocomplete="off"> Update
          </label>
          <label class="btn btn-outline-secondary active">
            <input type="radio" value="skip" name="stream' . $stream->get_stream_uid() . '" autocomplete="off" checked> Skip
          </label>
        </div>';
        $entry = [];
        $entry[] = $stream->getId();
        $entry[] = $action;
        $entry[] = $server->get_domain();
        $entry[] = $stream->get_port();
        $adminpassword = '<input type="text" class="form-control" name="stream' . $stream->get_stream_uid() . 'adminpw" value="' . $stream->get_adminpassword() . '" placeholder="Max 20 length">';
        $djpassword = '<input type="text" class="form-control" name="stream' . $stream->get_stream_uid() . 'djpw" value="' . $stream->get_djpassword() . '" placeholder="Max 20 length">';
        $entry[] = $adminpassword;
        $entry[] = $djpassword;
        $table_body[] = $entry;
    }
}
if (count($table_body) > 0) {
    $form = new form();
    $form->target("stream/bulkupdate");
    $form->col(12);
        $form->directAdd(render_datatable($table_head, $table_body));
    $this->output->setSwapTagString("page_content", $form->render("Process", "outline-warning"));
} else {
    $this->output->setSwapTagString("page_content", "No streams marked as need work");
}
