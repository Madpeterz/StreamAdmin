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
    protected function filterUrl(string $value, array $args = []): ?string
    {
        $this->failure = false;
        $this->testOK = true;
        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
            if (array_key_exists("isHTTP", $args)) {
                if (substr_count('http:', $value) == 1) {
                    return $value;
                }
                $this->whyfailed = "Requires HTTP protocall but failed that check.";
                $this->testOK = false;
            } elseif (array_key_exists("isHTTPS", $args)) {
                if (substr_count('https:', $value) == 1) {
                    return $value;
                }
                $this->whyfailed = "Requires HTTPS protocall but failed that check.";
                $this->testOK = false;
            }
            return $value;
        }
        return null;
    }
}
