<?php

namespace App\Framework;

use App\Config;
use App\Models\Datatable;
use YAPF\Bootstrap\Template\View;
use YAPF\InputFilter\InputFilter;

class Menu extends View
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
        $this->loadMenu();
        $this->addLib("jquery");
        $this->addLib("fontawesome");
        $this->addLib("bootstrap");
        $this->addLib("bootstrap-notify");
        $this->addLib("datatables");
    }

    protected function addLib(string $lib): void
    {
        if ($lib == "jquery") {
            $this->addJsScript("jquery", "jquery-3.4.1.min.js");
        } elseif ($lib == "bootstrap") {
            $this->addJsScript("popper", "popper.min.js");
            $this->addJsScript("bootstrap-4.4.1-dist/js", "bootstrap.min.js");
            $this->addCssScript("bootstrap-4.4.1-dist/css", "bootstrap.min.css");
        } elseif ($lib == "bootstrap-notify") {
            $this->addJsScript("bootstrap-notify-3.1.3/dist", "bootstrap-notify.min.js");
        } elseif ($lib == "fontawesome") {
            $this->addCssScript("fontawesome-free-5.12.1-web/css", "all.css");
        } elseif ($lib == "datatables") {
            $this->addCssScript("datatables", "datatables.min.css");
            $this->addJsScript("datatables", "datatables.min.js");
        }
    }

    protected function addCssScript(string $folder, string $file): void
    {
        $this->output->addSwapTagString(
            "html_cs_top",
            '<link rel="stylesheet" type="text/css" href="[[SITE_URL]]3rdparty/' . $folder . '/' . $file . '"/>'
        );
    }

    protected function addJsScript(string $folder, string $script): void
    {
        $this->output->addSwapTagString(
            "html_js_bottom",
            "<script src=\"[[SITE_URL]]3rdparty/" . $folder . "/" . $script . "\" type=\"text/javascript\"></script>"
        );
    }

    protected function renderDatatable(array $tableHead, array $tableBody, ?int $datatableID = null): string
    {
        $defaultRender = $this->renderTable($tableHead, $tableBody, "datatable-default display responsive");
        if ($datatableID === null) {
            return $defaultRender;
        }
        $datatableDriver = new Datatable();
        $datatableDriver->limitFields(["hideColZero","col","dir"]);
        if ($datatableDriver->loadID($datatableID) == false) {
            return $defaultRender;
        }
        $this->output->addSwapTagString("html_js_onready", "
        $('.customdatatable" . $datatableID . "').DataTable({
            'order': [[ " . $datatableDriver->getCol() . ", '" . $datatableDriver->getDir() . "' ]],
            responsive: true,
                  pageLength: " . $this->siteConfig->getSlConfig()->getDatatableItemsPerPage() . ",
                  lengthMenu: [[25, 10, 25, 50, -1], [\"Custom\", 10, 25, 50, \"All\"]],
            language: {
              searchPlaceholder: 'Search...',
              sSearch: '',
              lengthMenu: '_MENU_ items/page',
              }");
        if ($datatableDriver->getHideColZero() == true) {
            $this->output->addSwapTagString(
                "html_js_onready",
                ", 'columnDefs': [
                {
                    'targets': [ 0 ],
                    'visible': false,
                    'searchable': false
                }]"
            );
        }
        $this->output->addSwapTagString("html_js_onready", "});");
        return $this->renderTable($tableHead, $tableBody, "customdatatable" . $datatableID . " display responsive");
    }

    protected function addSwapTagString(string $tag, string $message): ?string
    {
        return $this->output->addSwapTagString($tag, $message);
    }

    protected function loadMenu(): void
    {
        if ($this->siteConfig->getSession()?->getLoggedIn() !== true) {
            return;
        }
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
                    "auditlog",
                ],
            ],
        ];
        $this->setSwapTag(
            "page_breadcrumb_icon",
            '<i class="fas fa-home text-success"></i>'
        );
        $this->setSwapTag(
            "page_breadcrumb_text",
            '<a href="[[SITE_URL]]">Dashboard</a>'
        );

        $output = "";
        foreach ($menu_items as $menu_key => $menu_config) {
            $output .= '<li class="nav-item">';
            $output .= '<a href="[[SITE_URL]]' . $menu_config["target"] . '" class="nav-link';
            if (in_array(strtolower($this->siteConfig->getModule()), $menu_config["active_on"]) == true) {
                $output .= " active";
                $this->setSwapTag(
                    "page_breadcrumb_icon",
                    '<i class="' . $menu_config["icon"] . ' text-success"></i>'
                );
                $this->setSwapTag(
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
