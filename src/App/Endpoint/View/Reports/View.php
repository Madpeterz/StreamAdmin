<?php

namespace App\Endpoint\View\Reports;

use App\Models\Template;
use App\Framework\View as BasicView;

abstract class View extends BasicView
{
    protected function amountChanged($old, $new): string
    {
        if ($old == 0) {
            if ($new != 0) {
                return "<span class=\"reports-gain\"> - </span>";
            }
            return "<span class=\"reports-loss\"> - </span>";
        }
        if ($new == 0) {
            return "<span class=\"reports-loss\"> - </span>";
        }
        if ($old < $new) {
            $change = $new - $old;
            return "<span class=\"reports-gain\">" . $change . "</span>";
        }
        $change = $old - $new;
        return "<span class=\"reports-loss\">" . $change . "</span>";
    }
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Reports");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->setSwapTag("page_actions", "");
    }
}
