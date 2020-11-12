<?php

namespace YAPF\Generator;

class GeneratorDefaults extends GeneratorTypes
{
    protected $string_types = ["varchar","text","char","longtext","mediumtext","tinytext","date","datetime"];
    protected $int_types = ["tinyint", "int","smallint","bigint","mediumint","enum","timestamp"];
    protected $float_types = ["decimal","float","double"];
    protected $known_types = [];
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
        $this->known_types = array_merge($this->string_types, $this->int_types, $this->float_types);
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
