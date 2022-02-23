<?php

namespace YAPF\Framework\Generator;

class GeneratorDefaults extends GeneratorTypes
{
    protected $tab_lookup = [];
    protected $file_lines = [];
    protected $output = "";

    public function getOutput(): string
    {
        return $this->output;
    }

    public function __construct()
    {
        $this->output = "";
        $this->UseTabs(true);
        parent::__construct();
    }

    public function useTabs($i_want_to_use_spaces_not_tabs = false): void
    {
        $this->tab_lookup = [0 => "",1 => "    ",2 => "        ",3 => "            "];
        if ($i_want_to_use_spaces_not_tabs == false) {
            $this->tab_lookup = [0 => "",1 => "\t",2 => "\t\t",3 => "\t\t\t"];
        }
    }
}
