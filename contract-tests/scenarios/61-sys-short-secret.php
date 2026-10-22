<?php

// sys.php refuses to work while the inbound secret is too short, even with a correctly signed token.
return [
    "description" => "System API with an invalid (too short) inbound secret",
    "seeds" => ["catalog", "short-secret"],
    "steps" => [
        ["id" => "notecard_short_secret", "kind" => "sys", "path" => "Notecard/Next"],
    ],
];
