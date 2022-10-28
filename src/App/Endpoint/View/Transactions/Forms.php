<?php

namespace App\Endpoint\View\Transactions;

use YAPF\Bootstrap\Template\Form;
use YAPF\Bootstrap\Template\Grid;

abstract class Forms extends RenderList
{
    protected $month = 1;
    protected $year = 2021;
    public function process(): void
    {
        parent::process();
        $this->output->addSwapTagString("page_content", "<hr/>");

        $grid = new Grid();
        $grid->addContent($this->rangeForm(), 5);
        $grid->addContent(" ", 2);
        $grid->addContent($this->avatarForm(), 5);
        $this->output->addSwapTagString("page_content", $grid->getOutput());
    }

    protected function avatarForm(): string
    {
        $form = new Form();
        $form->target("transactions/fromavatar");
        $form->mode("get");
        $form->required(true);
        $form->col(12);
        $form->group("By selected avatar");
        $form->textInput("avatarsearch", "Avatar", 126, "", "UUID or Name or UID");
        return $form->render("View", "primary");
    }
    protected function rangeForm(): string
    {
        $form = new Form();
        $form->target("transactions/inrange");
        $form->mode("get");
        $form->required(true);
        $form->col(12);
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
        return $form->render("View", "primary");
    }
}
