<?php 

namespace Tests;

session_start();
include "vendor/autoload.php";
include "tests/test.db.php";
include "src/App/Framework/globals.php";
include "src/App/Framework/url_loading.php";
include "src/App/Config/REQUIRE_ID_ON_LOAD.php";
include "src/App/Framework/install.php";
include "src/App/Framework/functions.php";