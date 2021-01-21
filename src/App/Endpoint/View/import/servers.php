<?php

$current_sql = $sql;
$old_sql = new mysqli_controler();
$old_sql->sqlStart_test($r4_db_username, $r4_db_pass, $r4_db_name, false, $r4_db_host);

$sql = $old_sql; // switch to r4

$r4_items = new r4_items_set();
$r4_items->loadAll();

$r4_items_servers = $r4_items->getUniqueArray("streamurl");

$sql = $current_sql; // swtich back to r7

$all_ok = true;
$created_servers = 0;
foreach ($r4_items_servers as $serverurl) {
    $server = new server();
    $server->setDomain($serverurl);
    $server->setControlPanelURL($serverurl);
    $server_status = $server->createEntry();
    if ($server_status["status"] == true) {
        $created_servers++;
    } else {
        $this->output->addSwapTagString("page_content", "Unable to create server because: " . $server_status["message"]);
        $all_ok = false;
        break;
    }
}
if ($all_ok == true) {
    $this->output->addSwapTagString("page_content", "Created: " . $created_servers . " servers <br/> <a href=\"[[url_base]]import\">Back to menu</a>");
} else {
    $sql->flagError();
}
