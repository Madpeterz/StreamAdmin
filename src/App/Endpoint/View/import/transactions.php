<?php

$current_sql = $sql;
$old_sql = new mysqli_controler();
$old_sql->sqlStart_test($r4_db_username, $r4_db_pass, $r4_db_name, false, $r4_db_host);

$sql = $old_sql; // switch to r4

$r4_sales_tracking_set = new r4_sales_tracking_set();
$r4_sales_tracking_set->loadAll();

$sql = $current_sql; // swtich back to r7

$avatars = new avatar_set();
$avatars->loadAll();

$avatarName_to_id = $avatars->getLinkedArray("avatarName", "id");

$all_ok = true;
$transactions_created = 0;

foreach ($r4_sales_tracking_set->getAllIds() as $r4_sales_tracking_id) {
    $r4_sales_tracking = $r4_sales_tracking_set->getObjectByID($r4_sales_tracking_id);

    $avatar_id = 1;
    if (array_key_exists($r4_sales_tracking->get_SLname(), $avatarName_to_id) == true) {
        $avatar_id = $avatarName_to_id[$r4_sales_tracking->get_SLname()];
    }

    $transaction = new transactions();
    $uid_transaction = $transaction->createUID("transactionUid", 8, 10);
    if ($uid_transaction["status"] == true) {
        $date = explode("/", $r4_sales_tracking->get_date());
        $time = explode(":", $r4_sales_tracking->get_time());
        $unixtime = time();
        if (count($date) == 3) {
            if (count($time) != 3) {
                $time = [0,0,1];
            }
            $unixtime = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
        }
        $transaction->setAvatarLink($avatar_id);
        $transaction->setPackageLink(null);
        $transaction->setStreamLink(null);
        $transaction->set_resellerLink(null);
        $transaction->set_regionLink(null);
        $transaction->set_amount($r4_sales_tracking->getAmount());
        $transaction->set_unixtime($unixtime);
        $transaction->set_transactionUid($uid_transaction["uid"]);
        $transaction->set_renew($r4_sales_tracking->get_salemode());
        $create_status = $transaction->createEntry();
        if ($create_status["status"] == true) {
            $transactions_created++;
        } else {
            $this->output->addSwapTagString("page_content", "Unable to create transaction because: " . $create_status["message"]);
            $all_ok = false;
            break;
        }
    } else {
        $this->output->addSwapTagString("page_content", "Unable to create transaction because: unable to assign it a uid");
        $all_ok = false;
        break;
    }
}
if ($all_ok == true) {
    $this->output->addSwapTagString("page_content", "Created: " . $transactions_created . " transactions <br/> <a href=\"[[url_base]]import\">Back to menu</a>");
} else {
    $sql->flagError();
}
