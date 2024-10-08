<?php

namespace App\Endpoint\View\Reports;

use App\Framework\Menu;

abstract class View extends Menu
{
    protected function amountChanged($old, $new): string
    {
        if (is_string($old) == true) {
            if (is_string($new) == false) {
                return "<span class=\"reports-gain\"> - </span>";
            }
            return "<span class=\"reports-loss\"> - </span>";
        }
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
