<?php

namespace App\Endpoints\View\Transactions;

use App\Template\Form;

abstract class RangeForm extends RenderList
{
    public function process(): void
    {
        parent::process();
        $this->output->addSwapTagString("page_content", "<hr/>");
        $form = new Form();
        $form->target("transactions/inrange");
        $form->mode("get");
        $form->required(true);
        $form->col(6);
        $form->group("Select transation period");
        $form->select(
            "month",
            "Month",
            $this->month,
            [1 => "Jan",
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
            ]
        );
        $start_year = 2013;
        $end_year = date("Y");
        $year_select = [];
        while ($start_year <= $end_year) {
            $year_select[$start_year] = $start_year;
            $start_year++;
        }
        $form->select("year", "Year", $this->year, $year_select);
        $this->output->addSwapTagString("page_content", $form->render("View", "primary"));
    }
}
