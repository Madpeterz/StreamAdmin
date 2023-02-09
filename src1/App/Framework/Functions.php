<?php

function nullSafeStrLen(?string $input): int
{
    if ($input == null) {
        return 0;
    }
    return strlen($input);
}
