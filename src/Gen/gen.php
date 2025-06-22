<?php

namespace App;

use YAPF\Framework\Generator\DbObjects;

include "../../vendor/autoload.php";
include "gen_db.php";

$system = new Config();
new DbObjects(["test"], saveToFolder:"../App/Models/(Set)", namespace:"App/Models(Set)", prefixDbName:false);
