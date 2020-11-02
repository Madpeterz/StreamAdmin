<?php

namespace YAPF\Generator;

class GeneratorDefaults extends GeneratorTypes
{
    protected $use_spaces_not_tabs = true;
    protected $string_types = ["varchar","text","char","longtext","mediumtext","tinytext","date","datetime"];
    protected $int_types = ["tinyint", "int","smallint","bigint","mediumint","enum","timestamp"];
    protected $float_types = ["decimal","float","double"];
    protected $known_types = [];
    protected $tab_lookup = [0 => "",1 => "    ",2 => "        ",3 => "            "];
    protected $file_lines = [];

    public function __construct(array $defaults = [])
    {
        $this->known_types = array_merge($this->string_types, $this->int_types, $this->float_types);
        if ($use_spaces_not_tabs == false) {
            $this->tab_lookup = [0 => "",1 => "\t",2 => "\t\t",3 => "\t\t\t"];
        }
        parent::__construct();
    }
}
