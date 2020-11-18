<?php

$this->output->addSwapTagString("page_title", "Toolbox");
$input = new inputFilter();
$month = $input->postFilter("month", "integer");
$year = $input->postFilter("year", "integer");
if ($year < 2013) {
    $year = date("Y");
}

if ($month < 1) {
    $month = 1;
} elseif ($month > 12) {
    $month = 12;
}
if ($year < 2013) {
    $year = 2013;
} elseif ($year > date("Y")) {
    $year = date("Y");
}

$start_unixtime = mktime(0, 0, 1, $month, 1, $year);
$end_month = $month + 1;
$end_year = $year;
if ($end_month > 12) {
    $end_year + 1;
    $end_month = 1;
}

$form = new form();
$form->target("reports/breakdown-month");
$form->mode("get");
$form->required(true);
$form->select("month", "Month", $month, [1 => "Jan",2 => "Feb",3 => "Mar",4 => "Apr",5 => "May",6 => "June",7 => "July",8 => "Aug",9 => "Sep",10 => "Oct",11 => "Nov",12 => "Dec"]);
$start_year = 2013;
$end_year = date("Y");
$year_select = [];
while ($start_year <= $end_year) {
    $year_select[$start_year] = $start_year;
    $start_year++;
}
$form->select("year", "Year", $year, $year_select);
$flow_form_month = $form->render("View", "primary");

$form = new form();
$form->target("reports/breakdown-year");
$form->mode("get");
$form->required(true);
$start_year = 2013;
$end_year = date("Y");
$year_select = [];
while ($start_year <= $end_year) {
    $year_select[$start_year] = $start_year;
    $start_year++;
}
$form->select("year", "Year", $year, $year_select);
$flow_form_year = $form->render("View", "primary");

$form = new form();
$form->target("reports/compare-years");
$form->mode("get");
$form->required(true);
$start_year = 2013;
$end_year = date("Y");
$year_select = [];
while ($start_year <= $end_year) {
    $year_select[$start_year] = $start_year;
    $start_year++;
}
$form->select("yeara", "A", $year, $year_select);
$form->select("yearb", "B", $year, $year_select);
$compare_form_year = $form->render("View", "primary");


// fast reports
$new_rentals = 0;
$renewed_rentals = 0;
$amount_new = 0;
$amount_renew = 0;

$transactions_set = new transactions_set();
$whereconfig = [
    "fields" => ["unixtime","unixtime"],
    "values" => [time() - $unixtime_week,time()],
    "types" => ["i","i"],
    "matches" => [">=","<="],
];

$transactions_set->load_with_config($whereconfig);
foreach ($transactions_set->getAllIds() as $transaction_id) {
    $transaction = $transactions_set->getObjectByID($transaction_id);
    if ($transaction->get_renew() == 1) {
        $renewed_rentals++;
        $amount_renew += $transaction->get_amount();
    } else {
        $new_rentals++;
        $amount_new += $transaction->get_amount();
    }
}
$mygrid = new grid();
$mygrid->add_content("<h3>This week</h3>" . render_table(["New","Renews","L$ total [New]","L$ total [Rewew]"], [[$new_rentals,$renewed_rentals,$amount_new,$amount_renew]]), 12);
$mygrid->add_content("<hr/><h3>Toolbox</h3><br/>", 12);
$mygrid->add_content("<h4>Month breakdown</h4>" . $flow_form_month, 6);
$mygrid->add_content("<h4>Year breakdown</h4>" . $flow_form_year, 6);
$mygrid->add_content("<hr/>", 12);
$mygrid->add_content("<h4>Year vs Year</h4>" . $compare_form_year, 6);
$this->output->setSwapTagString("page_content", $mygrid->get_output());
