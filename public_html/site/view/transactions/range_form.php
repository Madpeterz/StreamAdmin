<?php
echo "<hr/>";
$form = new form();
$form->target("transactions/inrange");
$form->mode("get");
$form->required(true);
$form->col(6);
    $form->group("Select transation period");
    $form->select("month","Month",$month,array(1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"May",6=>"June",7=>"July",8=>"Aug",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dec"));
    $start_year = 2013;
    $end_year = date("Y");
    $year_select = array();
    while($start_year <= $end_year)
    {
        $year_select[$start_year] = $start_year;
        $start_year++;
    }
    $form->select("year","Year",$year,$year_select);
echo $form->render("View","primary");
?>
