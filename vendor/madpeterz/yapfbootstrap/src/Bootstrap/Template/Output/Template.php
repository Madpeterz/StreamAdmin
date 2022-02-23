<?php

namespace YAPF\Bootstrap\Template\Output;

use YAPF\Bootstrap\ConfigBox\BootstrapConfigBox;

class Template extends SwapTags
{
    protected BootstrapConfigBox $config;

    protected $tempalte_parts = [];
    protected $render_layout = "[[topper]][[header]][[body_start]][[left_content]]"
    . "[[center_content]][[right_content]][[body_end]][[footer]]";
    protected $redirect_enabled = false;
    protected $redirect_offsite = false;
    protected $redirect_to = "";

    /**
     * getRedirectSettings
     * @return array<mixed>
     */
    public function getRedirectSettings(): array
    {
        return [
            "enabled" => $this->redirect_enabled,
            "offsite" => $this->redirect_offsite,
            "to" => $this->redirect_to,
        ];
    }

    protected function defaults(): void
    {
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

        $this->setSwapTag("SITE_NAME", $this->config->getSiteName());
        $this->setSwapTag("html_title_after", $this->config->getSiteName());
        $this->setSwapTag("html_title", "Page");
        $this->setSwapTag("html_cs_top", "");
        $this->setSwapTag("html_js_onready", "");
        $this->setSwapTag("html_js_bottom", "");
        $this->setSwapTag("cache_status", "Not used");
    }

    public function __construct(bool $with_defaults = true)
    {
        global $system;
        $this->config = $system;
        parent::__construct($with_defaults);
        if ($with_defaults == true) {
            $this->defaults();
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
        if ($offsite == false) {
            $this->redirect_to = $this->config->getSiteURL() . $to;
        }
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
        $check_for_file = $this->config->getRootFolder() . "/App/Theme/" . $layout . "/" . $bit . ".layout";
        if (file_exists($check_for_file) == true) {
            $this->tempalte_parts[$bit] = file_get_contents($check_for_file);
        }
    }
    public function renderAjax(): void
    {
        if ($this->redirect_enabled == true) {
            if ($this->redirect_offsite == true) {
                $this->setSwapTag("redirect", $this->redirect_to);
            } else {
                $this->setSwapTag("redirect", $this->redirect_to);
            }
        }
        $this->swaptags["render"] = "Ajax";
        print json_encode($this->swaptags);
    }
    protected function getCacheStatusMessage(): string
    {
        if ($this->config->getCacheDriver() == null) {
            return "N/A";
        }
        $this->config->getCacheDriver()->shutdown();
        $output = "Connected - Yes - ";
        if ($this->config->getCacheDriver()->getStatusConnected() == false) {
            $output = "Connected - No - ";
        }
        $output .= json_encode($this->config->getCacheDriver()->getStatusCounters());
        return $output;
    }
    public function renderPage(): void
    {
        $this->swaptags["render"] = "View";
        $this->setSwapTag("MODULE", $this->config->getModule());
        $this->setSwapTag("AREA", $this->config->getArea());
        $this->setSwapTag("PAGE", $this->config->getPage());
        $this->setSwapTag("cache_status", "Cache: " . $this->getCacheStatusMessage());

        if ($this->redirect_enabled == true) {
            if ($this->redirect_offsite == true) {
                if (!headers_sent()) {
                    header("Location: " . $this->redirect_to . "");
                } else {
                    print "<meta http-equiv=\"refresh\" content=\"0; url=" . $this->redirect_to . "\">";
                }
            } else {
                if (!headers_sent()) {
                    header("Location: " . $this->redirect_to);
                } else {
                    print " <meta http-equiv=\"refresh\" content=\"0; url=";
                    print $this->redirect_to . "\">";
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
    }
    public function tempateFull(): void
    {
        $this->loadTemplate(
            "full",
            ["full"]
        );
        $this->defaultSwapTags();
    }
    public function tempateAjax(): void
    {
        $this->render_layout = "";
        $this->setSwapTag("status", false);
        $this->setSwapTag("message", "Not processed");
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
}
