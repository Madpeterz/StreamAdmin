<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("rental_uid");
$rental = new rental();
$status = true;
if ($rental->load_by_field("rental_uid", $rental_uid) == true) {
    $detail = new detail();
    $where_fields = array(array("rentallink" => "="));
    $where_values = array(array($rental->get_id() => "i"));
    $count_data = $sql->basic_count($detail->get_table(), $where_fields, $where_values);
    if ($count_data["status"] == true) {
        if ($count_data["count"] == 0) {
            $detail = new detail();
            $detail->set_rentallink($rental->get_id());
            $create_status = $detail->create_entry();
            if ($create_status["status"] == true) {
                $status = true;
                echo $lang["details.rs.info.1"];
            } else {
                echo $lang["details.rs.error.4"];
            }
        } else {
            echo $lang["details.rs.error.3"];
        }
    } else {
        echo $lang["details.rs.error.2"];
    }
} else {
    echo $lang["details.rs.error.1"];
}
