<?php

/*
 * In-world entry point kept at its 2.0.x address (see routes/api.php). nginx runs files that
 * end in .php directly, so hand the request to Laravel as if it came through index.php; the
 * route still sees the original /xxx.php path.
 */
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/index.php';
