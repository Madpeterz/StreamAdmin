<?php

namespace App\Template;

use App\Framework\SessionControl;
use App\R7\Model\Slconfig;
use App\Template\Output\Template;
use YAPF\Core\ErrorControl\ErrorLogging;
use YAPF\MySQLi\MysqliEnabled;

abstract class BasicView extends ErrorLogging
{
    protected array $allowDisallow = [0 => "Disabled",1 => "Allow"];
    protected array $yesNo = [0 => "No",1 => "Yes"];
    protected array $disableEnable = [false => "Disabled",true => "Enabled"];
    protected Template $output;
    protected string $page;
    protected string $module;
    protected string $option;
    protected string $area;
    protected ?SessionControl $session;
    protected ?Slconfig $slconfig;
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
    public function forceSave(): void
    {
        if ($this->output->getSwapTagBool("status") == false) {
            $this->sql->sqlRollBack();
            return;
        }
        $this->sql->sqlSave(true);
    }
    public function process(): void
    {
        $this->setSwapTag("status", false);
        $this->failed("Not processsed yet");
    }
    protected function ok(string $message): void
    {
        $this->setMessage($message, true);
    }
    protected function failed(string $message): void
    {
        $this->setMessage($message, false);
    }
    protected function setMessage(string $message, bool $status): void
    {
        $this->setSwapTag("status", $status);
        $this->setSwapTag("message", $message);
    }
    protected bool $load_ok = true;
    public function getLoadOk(): bool
    {
        return $this->load_ok;
    }
    public function addVendor(string $vendor): void
    {
        $this->output->addVendor($vendor);
    }
    public function getOutputObject(): Template
    {
        return $this->output;
    }
    public function renderPage(): void
    {
        $this->output->renderPage();
    }
    public function getoutput(): void
    {
        $this->renderPage();
    }
    protected function setSwapTag($tag, $value): void
    {
        $this->output->setSwapTag($tag, $value);
    }
}
