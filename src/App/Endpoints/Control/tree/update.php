<?php

$input = new inputFilter();
$name = $input->postFilter("name");
$failed_on = "";
$this->output->setSwapTagString("redirect", "");
if (strlen($name) < 5) {
    $failed_on .= $lang["tree.up.error.1"];
} elseif (strlen($name) > 100) {
    $failed_on .= $lang["tree.up.error.2"];
}
$status = false;
if ($failed_on == "") {
    $treevender = new treevender();
    if ($treevender->loadID($this->page) == true) {
        $where_fields = [["name" => "="]];
        $where_values = [[$name => "s"]];
        $count_check = $sql->basic_count($treevender->get_table(), $where_fields, $where_values);
        $expected_count = 0;
        if ($treevender->getName() == $name) {
            $expected_count = 1;
        }
        if ($count_check["status"] == true) {
            if ($count_check["count"] == $expected_count) {
                $treevender->set_name($name);
                $update_status = $treevender->updateEntry();
                if ($update_status["status"] == true) {
                    $status = true;
                    $this->output->setSwapTagString("redirect", "tree");
                    $this->output->setSwapTagString("message", $lang["tree.up.info.1"]);
                } else {
                    $this->output->setSwapTagString("message", sprintf($lang["tree.up.error.6"], $update_status["message"]));
                }
            } else {
                $this->output->setSwapTagString("message", $lang["tree.up.error.5"]);
            }
        } else {
            $this->output->setSwapTagString("message", $lang["tree.up.error.4"]);
        }
    } else {
        $this->output->setSwapTagString("redirect", "tree");
        $this->output->setSwapTagString("message", $lang["tree.up.error.3"]);
    }
} else {
    $this->output->setSwapTagString("message", $failed_on);
}
