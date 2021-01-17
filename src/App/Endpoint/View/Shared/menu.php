<?php

    $menu_items = [
        "Dashboard" => [
            "icon" => "fas fa-home",
            "target" => "",
            "active_on" => ["home"],
        ],
        "Clients" => [
            "icon" => "fas fa-users",
            "target" => "client",
            "active_on" => ["client"],
        ],
        "Reports" => [
            "icon" => "fas fa-balance-scale-right",
            "target" => "reports",
            "active_on" => ["reports"],
        ],
        "Outbox" => [
            "icon" => "fas fa-mail-bulk",
            "target" => "outbox",
            "active_on" => ["outbox"],
        ],
        "Streams" => [
            "icon" => "fas fa-satellite-dish",
            "target" => "stream",
            "active_on" => ["stream"],
        ],
        "Packages" => [
            "icon" => "fas fa-box",
            "target" => "package",
            "active_on" => ["package"],
        ],
        "Resellers" => [
            "icon" => "fas fa-portrait",
            "target" => "reseller",
            "active_on" => ["reseller"],
        ],
        "TreeVend" => [
            "icon" => "fas fa-list-ul",
            "target" => "tree",
            "active_on" => ["tree"],
        ],
        "Config" => [
            "icon" => "fas fa-cogs",
            "target" => "config",
            "active_on" => [
                "banlist",
                "config",
                "template",
                "slconfig",
                "textureconfig",
                "avatar",
                "transactions",
                "staff",
                "notice",
                "objects",
                "server",
            ],
        ],
    ];

    $output = "";
    foreach ($menu_items as $menu_key => $menu_config) {
        $output .= '<li class="nav-item">';
        $output .= '<a href="[[url_base]]' . $menu_config["target"] . '" class="nav-link';
        if (in_array($module, $menu_config["active_on"]) == true) {
            $output .= " active";
            $this->output->addSwapTagString(
                "page_breadcrumb_icon",
                '<i class="' . $menu_config["icon"] . ' text-success"></i>'
            );
            $this->output->addSwapTagString(
                "page_breadcrumb_text",
                '<a href="[[url_base]]' . $menu_config["target"] . '">' . $menu_key . '</a>'
            );
        }
        $output .= '"><i class="' . $menu_config["icon"] . ' text-success"></i> ' . $menu_key . '</a>';
        $output .= '</li>';
    }
    $this->setSwapTag("html_menu", $output);
