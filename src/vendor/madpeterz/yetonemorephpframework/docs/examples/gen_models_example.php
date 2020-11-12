<?php

namespace App;

use YAPF\MySQLi\MysqliConnector as MysqliConnector;

include("examples/gen_models_example.config.php");
include("examples/gen_models_db.php");

// connect to SQL
$sql = new MysqliConnector();

// lets rock
$db_objects_factory = new DbObjectsFactory();
