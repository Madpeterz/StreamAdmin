<?php

namespace App\Template\Output;

use YAPF\InputFilter\InputFilter;

class Template extends Cache
{
    protected $tempalte_parts = [];
    protected $render_layout = "[[topper]][[header]][[body_start]][[left_content]]"
    . "[[center_content]][[right_content]][[body_end]][[footer]]";
    protected $redirect_enabled = false;
    protected $redirect_offsite = false;
    protected $redirect_to = "";
    public function __construct(bool $with_defaults = true)
    {
        parent::__construct($with_defaults);
        if ($with_defaults == true) {
            $this->tempalte_parts["topper"] = '<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>';
            $this->tempalte_parts["header"] = '<head><title>[[PAGE_TITLE]] - [[SITE_NAME]]</title>[[META_TAGS]]</head>';
            $this->tempalte_parts["body_start"] = "<body>";
            $this->tempalte_parts["center_content"] = "";
            $this->tempalte_parts["left_content"] = "";
            $this->tempalte_parts["right_content"] = "";
            $this->tempalte_parts["body_end"] = "</body>";
            $this->tempalte_parts["footer"] = "</html>";
        // global patch (to be phased out)
            global $site_theme, $site_lang, $template_parts;
            $this->setSwapTag("site_theme", $site_theme);
            $this->setSwapTag("site_lang", $site_lang);
            $this->setSwapTag("html_title", "Page");
            $this->setSwapTag("html_cs_top", "");
            $this->setSwapTag("html_js_onready", "");
            $this->setSwapTag("html_js_bottom", "");
            if (is_array($template_parts) == true) {
                if (array_key_exists("html_title_after", $template_parts) == true) {
                    $this->setSwapTag("html_title_after", $template_parts["html_title_after"]);
                    $this->siteName($template_parts["html_title_after"]);
                } else {
                    $this->setSwapTag("html_title_after", "StreamAdmin R7");
                    $this->siteName("StreamAdmin R7");
                }
                if (array_key_exists("url_base", $template_parts) == true) {
                    $this->setSwapTag("url_base", $template_parts["url_base"]);
                    $this->urlBase($template_parts["url_base"]);
                }
            } else {
                $this->setSwapTag("html_title_after", "StreamAdmin R7");
                $this->siteName("StreamAdmin R7");
            }
        }
    }
    public function redirectWithMessage(string $to, string $message, string $level = "info"): void
    {
        $to = "" . $to . "?bubblemessage=" . $message . "&bubbletype=" . $level;
        $this->redirect($to);
    }
    public function redirect(string $to, bool $offsite = false): void
    {
        $this->redirect_enabled = true;
        $this->redirect_offsite = $offsite;
        $this->redirect_to = $to;
    }
    public function loadTemplate(string $layout, array $layout_entrys): void
    {
        $this->render_layout = "";
        foreach ($layout_entrys as $entry) {
            $this->render_layout .= "[[" . $entry . "]]";
            $this->loadTemplateFile($layout, $entry);
        }
    }
    protected function loadTemplateFile(string $layout, string $bit): void
    {
        $check_for_file = "" . ROOTFOLDER . "/App/Theme/" . $layout . "/" . $bit . ".layout";
        if (file_exists($check_for_file) == true) {
            $this->tempalte_parts[$bit] = file_get_contents($check_for_file);
        }
    }
    public function renderAjax(): void
    {
        $this->swaptags["render"] = "Ajax";
        print json_encode($this->swaptags);
    }
    public function renderSecondlifeAjax(): void
    {
        $this->swaptags["render"] = "secondlifeAjax";
        foreach ($this->swaptags as $tag => $value) {
            if (in_array($value, ["true",true,1,"yes","True","TRUE"], true)) {
                $value = "1";
            } elseif (in_array($value, ["false",false,0,"no","False","FALSE"], true)) {
                $value = "0";
            }
            $this->swaptags[$tag] = $value;
        }
        print json_encode($this->swaptags);
    }
    public function renderPage(): void
    {
        global $page,$module,$area;
        $this->swaptags["render"] = "View";
        $this->setSwapTag("MODULE", $module);
        $this->setSwapTag("AREA", $area);
        $this->setSwapTag("PAGE", $page);

        $inputfilter = new InputFilter();
        $bubblemessage = htmlspecialchars($inputfilter->getFilter("bubblemessage"));
        $bubbletype = $inputfilter->getFilter("bubbletype");

        if (in_array($bubbletype, ["info","warning","danger","success"]) == true) {
            $this->addSwapTagString("html_js_onready", "alert_" . $bubbletype . "(\"" . $bubblemessage . "\");\n");
        }

        if ($this->redirect_enabled == true) {
            if ($this->redirect_offsite == true) {
                if (!headers_sent()) {
                    header("Location: " . $this->redirect_to . "");
                } else {
                    print "<meta http-equiv=\"refresh\" content=\"0; url=" . $this->redirect_to . "\">";
                }
            } else {
                if ($this->urlBase() == null) {
                    $this->urlBase("https://localhost");
                }
                if ($this->redirect_to == "here") {
                    $this->redirect_to = "";
                }
                if (!headers_sent()) {
                    header("Location: " . $this->urlBase() . "" . $this->redirect_to . "");
                } else {
                    print " < meta http - equiv = \"refresh\" content=\"0; url=";
                    print $this->urlBase() . "/" . $this->redirect_to . "\">";
                }
            }
        } else {
            $output = trim($this->render_layout);
            $output = $this->keypairReplace($output, $this->tempalte_parts);
            $output = $this->keypairReplace($output, $this->swaptags);
            $output = $this->keypairReplace($output, $this->swaptags);
            $output = $this->keypairReplace($output, $this->swaptags);
            $output = strtr($output, ["@NL@" => "\n\r"]);
            print $output;
        }
    }
    public function tempateSidemenu(): void
    {
        $this->loadTemplate(
            "sidemenu",
            ["topper","header","body_start","left_content","center_content","body_end","modals","footer"]
        );
        $this->defaultSwapTags();
        $this->addVendor("website");
        $this->loadMenu();
    }
    public function tempateFull(): void
    {
        $this->loadTemplate(
            "full",
            ["full"]
        );
        $this->defaultSwapTags();
        $this->addVendor("website");
    }
    public function tempateAjax(): void
    {
        $this->render_layout = "";
        $this->setSwapTag("status", false);
        $this->setSwapTag("message", "Not processed");
    }
    public function tempateSecondLifeAjax(): void
    {
        $this->tempateAjax();
    }
    public function tempateInstall(): void
    {
        $this->loadTemplate(
            "install",
            ["install"]
        );
        $this->defaultSwapTags();
        $this->addVendor("website");
    }
    protected function defaultSwapTags(): void
    {
        $this->setSwapTag("html_menu", "");
        $this->setSwapTag("page_title", "");
        $this->setSwapTag("page_actions", "");
        $this->setSwapTag("page_content", "");
        $this->setSwapTag("html_title", "");
        $this->setSwapTag("html_js_onready", "");
    }
    protected function loadMenu(): void
    {
        global $module;
        $menu_items = [
            "Dashboard" => [
                "icon" => "fas fa-home",
                "target" => "",
                "active_on" => ["home","",null],
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
            if (in_array(strtolower($module), $menu_config["active_on"]) == true) {
                $output .= " active";
                $this->addSwapTagString(
                    "page_breadcrumb_icon",
                    '<i class="' . $menu_config["icon"] . ' text-success"></i>'
                );
                $this->addSwapTagString(
                    "page_breadcrumb_text",
                    '<a href="[[url_base]]' . $menu_config["target"] . '">' . $menu_key . '</a>'
                );
            }
            $output .= '"><i class="' . $menu_config["icon"] . ' text-success"></i> ' . $menu_key . '</a>';
            $output .= '</li>';
        }
        $this->setSwapTag("html_menu", $output);
    }
}
