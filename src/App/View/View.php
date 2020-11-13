<?php

namespace App;

use App\Template\Template as Templated;

abstract class View
{
    protected Templated $output;
    protected $page;
    protected $module;
    protected $option;
    protected $session;
    protected $slconfig;
    protected $sql;
    public function __construct()
    {
        global $slconfig, $page, $module, $option, $session, $sql;
        $this->page = &$page;
        $this->module = &$module;
        $this->option = &$option;
        $this->session = &$session;
        $this->slconfig = &$slconfig;
        $this->sql = &$sql;
    }
    public function process()
    {
        $this->output->addSwapTagString("html_title", "");
        $this->output->setSwapTagString("page_title", " not set");
        $this->output->setSwapTagString("page_actions", "");
        $this->output->addSwapTagString("page_content", "Not Loaded");
    }
}
