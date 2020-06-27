<?php
$template_parts["html_title"] .= " ~ Manage";
$template_parts["page_title"] .= "Editing client";
$template_parts["page_actions"] = "<a href='[[url_base]]client/remove/".$page."'><button type='button' class='btn btn-danger'>Remove</button></a>";

$rental = new rental();
if($rental->load_by_field("rental_uid",$page) == true)
{
    $avatar = new avatar();
    $avatar->load($rental->get_avatarlink());
    $template_parts["page_title"] .= ": ".$rental->get_rental_uid()." [".$avatar->get_avatarname()."]";
    $form = new form();
    $form->target("client/update/".$page."");
    $form->required(true);
    $form->col(6);
        $form->group("Timeleft: ".timeleft_hours_and_days($rental->get_expireunixtime())."");
        $form->number_input("adjustment_days","Adjustment [Days]",0,3,"Max 999");
        $form->number_input("adjustment_hours","Adjustment [Hours]",0,2,"Max 23");
        $form->select("adjustment_dir","Adjustment (Type)",false,array(false=>"Remove",true=>"Add"));
    $form->col(6);
        $form->group("Transfer");
        $form->text_input("transfer_avataruid","Avatar UID <a href=\"[[url_base]]avatar\" target=\"_blank\">Find</a>",8,"","Avatar UID (Not SL UUID)");
    $form->split();
    $form->col(6);
        $form->group("Message");
        $form->textarea("message","Message",9999,$rental->get_message(),"Any rental with a message will not be listed on the Fast removal system! Max length 9999");
    echo $form->render("Update","primary");

    $where_config = array(
        "fields" => array("streamlink","unixtime"),
        "values" => array($rental->get_streamlink(),$rental->get_startunixtime()),
        "types" => array("i","i"),
        "matches" => array("=",">="),
    );
    $order_by = array("ordering_enabled"=>true,"order_field"=>"unixtime","order_dir"=>"DESC");
    $transactions_set = new transactions_set();
    $transactions_set->load_with_config($where_config,$order_by);

    $reseller_set = new reseller_set();
    $region_set = new region_set();
    $avatar_set = new avatar_set();
    $region_set->load_ids($transactions_set->get_all_by_field("regionlink"));
    $reseller_set->load_ids($transactions_set->get_all_by_field("resellerlink"));
    $avatar_set->load_ids(array_merge($transactions_set->get_all_by_field("avatarlink"),$reseller_set->get_all_by_field("avatarlink")),"id","i",false);

    $table_head = array("id","Transaction UID","Avatar","Reseller","Region","Amount","Datetime");
    $table_body = array();
    foreach($transactions_set->get_all_ids() as $transaction_id)
    {
        $transaction = $transactions_set->get_object_by_id($transaction_id);
        $avatar = $avatar_set->get_object_by_id($transaction->get_avatarlink());
        $region = $region_set->get_object_by_id($transaction->get_regionlink());
        $reseller = $reseller_set->get_object_by_id($transaction->get_resellerlink());
        $reseller_av = $avatar_set->get_object_by_id($reseller->get_avatarlink());
        $entry = array();
        $entry[] = $transaction->get_id();
        $entry[] = $transaction->get_transaction_uid();
        $entry[] = $avatar->get_avatarname();
        $entry[] = $reseller_av->get_avatarname();
        $entry[] = $region->get_name();
        $entry[] = $transaction->get_amount();
        $entry[]  = date('l jS \of F Y h:i:s A',$transaction->get_unixtime());
        $table_body[] = $entry;
    }
    echo "<br/><hr/><h4>Transactions</h4>";
    echo render_datatable($table_head,$table_body);
}
else
{
    redirect("client?bubblemessage=unable to find client&bubbletype=warning");
}
?>