<?php

namespace App\Endpoint\View\Reports;

use YAPF\Bootstrap\Template\Form;
use YAPF\Bootstrap\Template\Grid;
use App\Models\Sets\TransactionsSet;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", "Toolbox");

        $month = $this->input->get("month")->asInt();
        $year = $this->input->get("year")->asInt();
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

        $end_month = $month + 1;
        $end_year = $year;
        if ($end_month > 12) {
            $end_year + 1;
            $end_month = 1;
        }

        $form = new Form();
        $form->target("reports/BreakdownMonth");
        $form->mode("get");
        $form->required(true);
        $monthtonum = [
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "June",
            7 => "July",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec",
        ];
        $form->select("month", "Month", $month, $monthtonum);
        $start_year = 2013;
        $end_year = date("Y");
        $year_select = [];
        while ($start_year <= $end_year) {
            $year_select[$start_year] = $start_year;
            $start_year++;
        }
        $form->select("year", "Year", $year, $year_select);
        $flow_form_month = $form->render("View", "primary");

        $form = new Form();
        $form->target("reports/BreakdownYear");
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

        $form = new Form();
        $form->target("reports/ComapreYears");
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

        $transactions_set = new TransactionsSet();
        $whereconfig = [
            "fields" => ["unixtime","unixtime"],
            "values" => [time() - $this->siteConfig->unixtimeWeek(),time()],
            "types" => ["i","i"],
            "matches" => [">=","<="],
        ];

        $transactions_set->loadWithConfig($whereconfig);
        foreach ($transactions_set as $transaction) {
            if ($transaction->getRenew() == 1) {
                $renewed_rentals++;
                $amount_renew += $transaction->getAmount();
                continue;
            }
            $new_rentals++;
            $amount_new += $transaction->getAmount();
        }
        $mygrid = new Grid();
        $table = $this->renderTable(
            ["New","Renews","L$ total [New]","L$ total [Rewew]"],
            [[$new_rentals,$renewed_rentals,$amount_new,$amount_renew]]
        );
        $mygrid->addContent(
            "<h3>This week</h3>" .
            $table,
            12
        );
        $mygrid->addContent("<hr/><h3>Toolbox</h3><br/>", 12);
        $mygrid->addContent("<h4>Month breakdown</h4>" . $flow_form_month, 6);
        $mygrid->addContent("<h4>Year breakdown</h4>" . $flow_form_year, 6);
        $mygrid->addContent("<hr/>", 12);
        $mygrid->addContent("<h4>Year vs Year</h4>" . $compare_form_year, 6);
        $this->setSwapTag("page_content", $mygrid->getOutput());
    }
}
