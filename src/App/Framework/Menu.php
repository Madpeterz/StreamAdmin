<?php

namespace App\Framework;

use App\Config;
use YAPF\Bootstrap\Template\TableView;
use YAPF\InputFilter\InputFilter;

class Menu extends TableView
{
    protected Config $siteConfig;
    protected InputFilter $input;
    public function __construct(
        bool $AutoLoadTemplate = true
    ) {
        global $system;
        $this->siteConfig = $system;
        $this->input = new InputFilter();
        parent::__construct($AutoLoadTemplate);
    }

    protected function renderDatatable(array $tableHead, array $tableBody): string
    {
        return $this->renderTable($tableHead, $tableBody);
    }

    protected function addSwapTagString(string $tag, string $message): ?string
    {
        return $this->output->addSwapTagString($tag, $message);
    }

    protected function loadMenu(): void
    {
        $menu_items = [
            "Dashboard" => [
                "icon" => "fas fa-home",
                "target" => "",
                "active_on" => ["home","",null],
            ],
            "Clients" => [
                "icon" => "fas fa-users",
                "target" => "Client",
                "active_on" => ["client"],
            ],
            "Reports" => [
                "icon" => "fas fa-balance-scale-right",
                "target" => "Reports",
                "active_on" => ["reports"],
            ],
            "Outbox" => [
                "icon" => "fas fa-mail-bulk",
                "target" => "Outbox",
                "active_on" => ["outbox"],
            ],
            "Streams" => [
                "icon" => "fas fa-satellite-dish",
                "target" => "Stream",
                "active_on" => ["stream"],
            ],
            "Packages" => [
                "icon" => "fas fa-box",
                "target" => "Package",
                "active_on" => ["package"],
            ],
            "Resellers" => [
                "icon" => "fas fa-portrait",
                "target" => "Reseller",
                "active_on" => ["reseller"],
            ],
            "TreeVend" => [
                "icon" => "fas fa-list-ul",
                "target" => "Tree",
                "active_on" => ["tree"],
            ],
            "Config" => [
                "icon" => "fas fa-cogs",
                "target" => "Config",
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
                    "datatables",
                    "export",
                ],
            ],
        ];

        $output = "";
        foreach ($menu_items as $menu_key => $menu_config) {
            $output .= '<li class="nav-item">';
            $output .= '<a href="[[SITE_URL]]' . $menu_config["target"] . '" class="nav-link';
            if (in_array(strtolower($this->siteConfig->getModule()), $menu_config["active_on"]) == true) {
                $output .= " active";
                $this->addSwapTagString(
                    "page_breadcrumb_icon",
                    '<i class="' . $menu_config["icon"] . ' text-success"></i>'
                );
                $this->addSwapTagString(
                    "page_breadcrumb_text",
                    '<a href="[[SITE_URL]]' . $menu_config["target"] . '">' . $menu_key . '</a>'
                );
            }
            $output .= '"><i class="' . $menu_config["icon"] . ' text-success"></i> ' . $menu_key . '</a>';
            $output .= '</li>';
        }
        $this->setSwapTag("html_menu", $output);
    }
}
