<?php
$input = new inputFilter();
$year = $input->postFilter("year","integer");
if($year < 2013) $year = 2013;
else if($year > date("Y")) $year=date("Y");

$template_parts["page_title"] .= "Year breakdown: ".$year."";

$transactions_set = new transactions_set();
$whereconfig = array(
    "fields" => array("unixtime","unixtime"),
    "values" => array(mktime(0,0,1,1,1,$year),mktime(23,59,59,12,cal_days_in_month(CAL_GREGORIAN, 12, $year),$year)),
    "types" => array("i","i"),
    "matches" => array(">=","<="),
);
$transactions_set->load_with_config($whereconfig);

// totals
$new_rentals = 0;
$renewed_rentals = 0;
$amount_new = 0;
$amount_renew = 0;

$lookups = array(
    mktime(23,59,59,1,cal_days_in_month(CAL_GREGORIAN, 1, $year),$year)=>1,
    mktime(23,59,59,2,cal_days_in_month(CAL_GREGORIAN, 2, $year),$year)=>2,
    mktime(23,59,59,3,cal_days_in_month(CAL_GREGORIAN, 3, $year),$year)=>3,
    mktime(23,59,59,4,cal_days_in_month(CAL_GREGORIAN, 4, $year),$year)=>4,
    mktime(23,59,59,5,cal_days_in_month(CAL_GREGORIAN, 5, $year),$year)=>5,
    mktime(23,59,59,6,cal_days_in_month(CAL_GREGORIAN, 6, $year),$year)=>6,
    mktime(23,59,59,7,cal_days_in_month(CAL_GREGORIAN, 7, $year),$year)=>7,
    mktime(23,59,59,8,cal_days_in_month(CAL_GREGORIAN, 8, $year),$year)=>8,
    mktime(23,59,59,9,cal_days_in_month(CAL_GREGORIAN, 9, $year),$year)=>9,
    mktime(23,59,59,10,cal_days_in_month(CAL_GREGORIAN, 10, $year),$year)=>10,
    mktime(23,59,59,11,cal_days_in_month(CAL_GREGORIAN, 11, $year),$year)=>11,
    mktime(23,59,59,12,cal_days_in_month(CAL_GREGORIAN, 12, $year),$year)=>12,
);

$month_datasets = array(
    1=>array("title"=>"Jan","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    2=>array("title"=>"Feb","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    3=>array("title"=>"Mar","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    4=>array("title"=>"Apr","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    5=>array("title"=>"May","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    6=>array("title"=>"June","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    7=>array("title"=>"July","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    8=>array("title"=>"Aug","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    9=>array("title"=>"Sep","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    10=>array("title"=>"Oct","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    11=>array("title"=>"Nov","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
    12=>array("title"=>"Dec","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0),
);


foreach($transactions_set->get_all_ids() as $transaction_id)
{
    $transaction = $transactions_set->get_object_by_id($transaction_id);
    $month_id = 1;
    foreach($lookups as $max_unixtime => $month_num)
    {
        if($transaction->get_unixtime() < $max_unixtime)
        {
            $month_id = $month_num;
            break;
        }
    }
    $month_datasets[$month_id]["sum"] += $transaction->get_amount();
    $month_datasets[$month_id]["counted"] += 1;
    if($transaction->get_renew() == 1)
    {
        $month_datasets[$month_id]["renew"] += 1;
        $month_datasets[$month_id]["amount_renew"] += $transaction->get_amount();
        $renewed_rentals++;
        $amount_renew += $transaction->get_amount();
    }
    else
    {
        $month_datasets[$month_id]["new"] += 1;
        $month_datasets[$month_id]["amount_new"] += $transaction->get_amount();
        $new_rentals++;
        $amount_new += $transaction->get_amount();
    }
}


$last_month = array("title"=>"None","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0);
$best_month = array("title"=>"None","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0);

$best_month_total = 0;
foreach($month_datasets as $index => $dataset)
{
    $sum = $dataset["amount_new"] + $dataset["amount_renew"];
    if($sum > $best_month_total)
    {
        $best_month = $dataset;
        $best_month_total = $sum;
    }
}
function amount_changed($old,$new)
{
    if($old == 0)
    {
        if($new != 0)
        {
            return "<span class=\"reports-gain\">100 %</span>";
        }
        else
        {
            return " / ";
        }
    }
    else
    {
        if($new == 0)
        {
            return "<span class=\"reports-loss\">100 %</span>";
        }
        else
        {
            $change = number_format((1 - $old / $new) * 100,2);
            if($change > 0)
            {
                return "<span class=\"reports-gain\">".$change." %</span>";
            }
            else
            {
                return "<span class=\"reports-loss\">".$change." %</span>";
            }
        }
    }
}
$table_head = array("Month","L$ total","Transactions total","Count / New","Count / Renew","L$ / total new","L$ total renew","Change from last month","Change from best month");
$table_body = array();
foreach($month_datasets as $index => $dataset)
{
    $entry = array();
    if($dataset["sum"] > 0)
    {
        $entry[] = $dataset["title"];
        $entry[] = $dataset["sum"];
        $entry[] = $dataset["counted"];
        $entry[] = $dataset["new"];
        $entry[] = $dataset["renew"];
        $entry[] = $dataset["amount_new"];
        $entry[] = $dataset["amount_renew"];
        if($last_month["title"] == "None")
        {
            $entry[] = "-";
        }
        else
        {
            $entry[] = amount_changed($last_month["sum"],$dataset["sum"]);
        }
        if($dataset["title"] != $best_month["title"])
        {
            $entry[] = amount_changed($best_month["sum"],$dataset["sum"]);
        }
        else
        {
            $entry[] = " / ";
        }
        $last_month = $dataset;
        if($dataset["title"] == $best_month["title"])
        {
            $new_entry = array();
            $counter = 0;
            foreach($entry as $point)
            {
                if($counter < 7)
                {
                    $new_entry[] = "<span class=\"reports-best\">".$point."</span>";
                }
                else
                {
                    $new_entry[] = $point;
                }
                $counter++;
            }
            $entry = $new_entry;
        }
        $table_body[] = $entry;
    }

}
$pages = array();
$pages["Fast report"] = render_table(array("New","Renews","L$ total [New]","L$ total [Rewew]"),array(array($new_rentals,$renewed_rentals,$amount_new,$amount_renew)));
$pages["Month breakdown"] = render_table($table_head,$table_body);
$paged_info = new paged_info();
print $paged_info->render($pages);
?>
