<?php

namespace YAPF\DbObjects\GenClass;

use YAPF\Core\SqlConnectedClass as SqlConnectedClass;

abstract class GenClassCore extends SqlConnectedClass
{
    protected $use_table = "";
    protected $save_dataset = [];
    protected $dataset = [];
    protected $allow_set_field = true;
    public $bad_id = false;
    public $use_id_field = "";
}
