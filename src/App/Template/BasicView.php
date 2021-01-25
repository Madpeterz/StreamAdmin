<?php

namespace App\Template;

use App\Framework\SessionControl;
use App\Models\Slconfig;
use App\Template\Output\Template;
use YAPF\MySQLi\MysqliEnabled;

abstract class BasicView
{
    protected Array $allowDisallow = [0 => "Disabled",1 => "Allow"];
    protected Array $yesNo = [0 => "No",1 => "Yes"];
    protected Array $disableEnable = [false => "Disabled",true => "Enabled"];
    protected Template $output;
    protected string $page;
    protected string $module;
    protected string $option;
    protected string $area;
    protected SessionControl $session;
    protected Slconfig $slconfig;
    protected ?MysqliEnabled $sql;
    public function __construct(bool $AutoLoadTemplate = true)
    {
        global $slconfig, $page, $area, $module, $optional, $session, $sql;
        $this->page = &$page;
        $this->module = &$module;
        $this->area = &$area;
        $this->option = &$optional;
        $this->session = &$session;
        $this->slconfig = &$slconfig;
        $this->sql = &$sql;
        $this->output = new Template($AutoLoadTemplate);
        if ($AutoLoadTemplate == true) {
            $this->output->tempateSidemenu();
        }
    }
    public function addVendor(string $vendor): void
    {
        $this->output->addVendor($vendor);
    }
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", "");
        $this->setSwapTag("page_title", " not set");
        $this->setSwapTag("page_actions", "");
        $this->output->addSwapTagString("page_content", "Not Loaded");
        $this->setSwapTag("status", "false");
    }
    public function getOutputObject(): Template
    {
        return $this->output;
    }
    public function getoutput(): void
    {
        $this->output->renderPage();
    }
    protected function setSwapTag(string $tag, $value): void
    {
        $this->output->setSwapTag($tag, $value);
    }
}
