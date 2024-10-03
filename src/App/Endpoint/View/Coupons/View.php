<?php

namespace App\Endpoint\View\Coupons;

use App\Framework\Menu;

abstract class View extends Menu
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Coupons");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Coupons");
        $this->setSwapTag(
            "page_actions",
            " "
        );
        if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
            $this->setSwapTag(
                "page_actions",
                "<a href='[[SITE_URL]]Coupons/Create'><button type='button' class='btn btn-success'>Create</button></a>"
            );
        }
    }
}
