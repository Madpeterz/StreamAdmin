<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeJson extends InputFilterTypeUUID
{
    /**
     * filterJson
     * checks to see if the value can be decoded
     * into a json object
     * @return mixed[] or null
     */
    protected function filterJson(string $value, array $args = []): ?array
    {
        $this->whyfailed = "";
        $json = json_decode($value, true);
        if (($json === false) || ($json === null)) {
            $this->whyfailed = "Not a vaild json object string";
            return null;
        } else {
            return $json;
        }
    }
}
