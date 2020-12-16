<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("rental_uid");
$rental = new rental();
$status = true;
if ($rental->loadByField("rental_uid", $rental_uid) == true) {
    $detail = new detail();
    $where_fields = [["rentallink" => "="]];
    $where_values = [[$rental->getId() => "i"]];
    $count_data = $sql->basic_count($detail->get_table(), $where_fields, $where_values);
    if ($count_data["status"] == true) {
        if ($count_data["count"] == 0) {
            $detail = new detail();
            $detail->setRentallink($rental->getId());
            $create_status = $detail->createEntry();
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
