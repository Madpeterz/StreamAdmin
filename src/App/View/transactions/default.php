<?php

$transaction_set = new transactions_set();
$transaction_set->load_newest(30);

$package_set = new package_set();
$region_set = new region_set();
$avatar_set = new avatar_set();

$package_set->loadIds($transaction_set->getAllByField("packagelink"));
$region_set->loadIds($transaction_set->getAllByField("regionlink"));
$avatar_set->loadIds($transaction_set->getAllByField("avatarlink"));

$this->output->addSwapTagString("page_title", " Newest 30");
include "webpanel/view/transactions/render_list.php";
include "webpanel/view/transactions/range_form.php";
