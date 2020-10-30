<?php

namespace Madpeterz\YAPF\DB_OBJECTS;

use Madpeterz\YAPF\SqlConnectedClass as SqlConnectedClass;

abstract class GenClassCore extends SqlConnectedClass
{
    protected $use_table = "";
    protected $save_dataset = [];
    protected $dataset = [];
    protected $allow_set_field = true;
    public $bad_id = false;
    public $use_id_field = "";
}
