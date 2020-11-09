<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeHttp extends InputFilterTypeJson
{
    /**
     * filterUrl
     * checks to see if the given input is a url
     * can also enforce protocall with
     * isHTTP and isHTTPS args.
     */
    protected function setAndAtStart(string $value, string $match): ?string
    {
        $pos = strpos($value, $match);
        if ($pos === false) {
            $this->whyfailed = $match . " is missing from the value!";
            return null;
        } elseif ($pos != 0) {
            $this->whyfailed = $match . " is missing from the start of the value!";
            return null;
        }
        return $value;
    }
    protected function filterUrl(string $value, array $args = []): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
            if (array_key_exists("isHTTP", $args)) {
                return $this->setAndAtStart($value, "http://");
            } elseif (array_key_exists("isHTTPS", $args)) {
                return $this->setAndAtStart($value, "https://");
            }
            return $value;
        }
        return null;
    }
}
