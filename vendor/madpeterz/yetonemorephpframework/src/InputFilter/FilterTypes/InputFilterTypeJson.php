<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeJson extends InputFilterTypeUUID
{
    /**
     * filterJson
     * checks to see if the value can be decoded
     * into a json object
     * @return stdClass or null
     */
    protected function filterJson(string $value): ?stdClass
    {
        $json = json_decode($value, true);
        if (($json === false) || ($json === null)) {
            return null;
        } else {
            return $json;
        }
    }
}
