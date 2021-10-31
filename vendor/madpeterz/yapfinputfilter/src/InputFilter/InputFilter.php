<?php

namespace YAPF\InputFilter;

class InputFilter extends Filters
{
    public function postUUID(string $inputName): ?string
    {
        return $this->sharedInputFilter($inputName, "POST", "uuid", []);
    }
    public function postString(
        string $inputName,
        ?int $maxLength = null,
        ?int $minlength = null
    ): ?string {
        $args  = [];
        if ($maxLength != null) {
            $args["maxLength"] = $maxLength;
        }
        if ($minlength != null) {
            $args["minLength"] = $minlength;
        }
        return $this->sharedInputFilter($inputName, "POST", "string", $args);
    }
    public function postInteger(
        string $inputName,
        bool $zeroCheck = false,
        bool $greaterThanZero = false,
        ?int $max = null,
        ?int $min = null
    ): ?int {
        $args  = [];
        if ($zeroCheck != false) {
            $args["zeroCheck"] = "Enabled";
        } elseif ($greaterThanZero != false) {
            $args["gtr0"] = "Enabled";
        }
        $args["min"] = $min;
        $args["max"] = $max;
        return $this->sharedInputFilter($inputName, "POST", "integer", $args);
    }
    public function postFloat(string $inputName, bool $zeroCheck = false): ?float
    {
        $args  = [];
        if ($zeroCheck != false) {
            $args["zeroCheck"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "POST", "float", $args);
    }
    /**
     * postCheckbox
     * @return mixed return changes based on the selected filter.
     */
    public function postCheckbox(string $inputName, string $useFilter = "integer")
    {
        return $this->sharedInputFilter($inputName, "POST", $useFilter);
    }
    public function postBool(string $inputName): ?bool
    {
        return $this->sharedInputFilter($inputName, "POST", "bool", []);
    }
    public function postVector(string $inputName, bool $strictChecks = false): ?string
    {
        $args = [];
        if ($strictChecks == true) {
            $args["stricstrictt"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "POST", "vector", $args);
    }
    public function postDate(string $inputName, bool $asUNIX = false, bool $humanReadable = false): ?string
    {
        $args = [];
        if ($asUNIX == true) {
            $args["asUNIX"] = "Enabled";
        } elseif ($humanReadable == true) {
            $args["humanReadable"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "POST", "date", $args);
    }
    public function postEmail(string $inputName, bool $noMailboxs = false): ?string
    {
        $args = [];
        if ($noMailboxs == true) {
            $args["no_mailboxs"] = "no_mailboxs";
        }
        return $this->sharedInputFilter($inputName, "POST", "email", $args);
    }
    public function postUrl(string $inputName, bool $isHTTPS = false, bool $isHTTP = false): ?string
    {
        $args = [];
        if ($isHTTPS == true) {
            $args["isHTTPS"] = "Enabled";
        } elseif ($isHTTP == true) {
            $args["isHTTP"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "POST", "url", $args);
    }
    /**
     * getColour
     * @return mixed or mixed[] or null
     */
    public function postColour(string $inputName, bool $isHEX = false, bool $isRGB = false): ?string
    {
        return $this->postColor($inputName, $isHEX, $isRGB);
    }
    /**
     * getColor
     * @return mixed or mixed[] or null
     */
    public function postColor(string $inputName, bool $isHEX = false, bool $isRGB = false): ?string
    {
        $args = [];
        if ($isHEX == true) {
            $args["isHEX"] = "Enabled";
        } elseif ($isRGB == true) {
            $args["isRGB"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "POST", "color", $args);
    }
    public function postTrueFalse(string $inputName): ?bool
    {
        return $this->getBool($inputName);
    }
    /**
     * getJson
     * @return mixed[] or null
     */
    public function postJson(string $inputName): ?array
    {
        return $this->sharedInputFilter($inputName, "POST", "json", []);
    }
    /**
     * getArray
     * @return mixed[] or null
     */
    public function postArray(string $inputName): ?array
    {
        return $this->sharedInputFilter($inputName, "POST", "array", []);
    }

    public function getUUID(string $inputName): ?string
    {
        return $this->sharedInputFilter($inputName, "GET", "uuid", []);
    }
    public function getString(
        string $inputName,
        ?int $maxLength = null,
        ?int $minlength = null
    ): ?string {
        $args  = [];
        if ($maxLength != null) {
            $args["maxLength"] = $maxLength;
        }
        if ($minlength != null) {
            $args["minLength"] = $minlength;
        }
        return $this->sharedInputFilter($inputName, "GET", "string", $args);
    }
    public function getInteger(
        string $inputName,
        bool $zeroCheck = false,
        bool $greaterThanZero = false,
        ?int $max = null,
        ?int $min = null
    ): ?int {
        $args  = [];
        if ($zeroCheck != false) {
            $args["zeroCheck"] = "Enabled";
        } elseif ($greaterThanZero != false) {
            $args["gtr0"] = "Enabled";
        }
        $args["min"] = $min;
        $args["max"] = $max;
        return $this->sharedInputFilter($inputName, "GET", "integer", $args);
    }
    public function getFloat(string $inputName, bool $zeroCheck = false): ?float
    {
        $args  = [];
        if ($zeroCheck != false) {
            $args["zeroCheck"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "GET", "float", $args);
    }
    /**
     * getCheckbox
     * @return mixed return changes based on the selected filter.
     */
    public function getCheckbox(string $inputName, string $useFilter = "integer")
    {
        return $this->sharedInputFilter($inputName, "GET", $useFilter);
    }
    public function getBool(string $inputName): ?bool
    {
        return $this->sharedInputFilter($inputName, "GET", "bool", []);
    }
    public function getVector(string $inputName, bool $strictChecks = false): ?string
    {
        $args = [];
        if ($strictChecks == true) {
            $args["stricstrictt"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "GET", "vector", $args);
    }
    public function getDate(string $inputName, bool $asUNIX = false, bool $humanReadable = false): ?string
    {
        $args = [];
        if ($asUNIX == true) {
            $args["asUNIX"] = "Enabled";
        } elseif ($humanReadable == true) {
            $args["humanReadable"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "GET", "date", $args);
    }
    public function getEmail(string $inputName, bool $noMailboxs = false): ?string
    {
        $args = [];
        if ($noMailboxs == true) {
            $args["no_mailboxs"] = "no_mailboxs";
        }
        return $this->sharedInputFilter($inputName, "GET", "email", $args);
    }
    public function getUrl(string $inputName, bool $isHTTPS = false, bool $isHTTP = false): ?string
    {
        $args = [];
        if ($isHTTPS == true) {
            $args["isHTTPS"] = "Enabled";
        } elseif ($isHTTP == true) {
            $args["isHTTP"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "GET", "url", $args);
    }
    /**
     * getColour
     * @return mixed or mixed[] or null
     */
    public function getColour(string $inputName, bool $isHEX = false, bool $isRGB = false): ?string
    {
        return $this->getColor($inputName, $isHEX, $isRGB);
    }
    /**
     * getColor
     * @return mixed or mixed[] or null
     */
    public function getColor(string $inputName, bool $isHEX = false, bool $isRGB = false): ?string
    {
        $args = [];
        if ($isHEX == true) {
            $args["isHEX"] = "Enabled";
        } elseif ($isRGB == true) {
            $args["isRGB"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, "GET", "color", $args);
    }
    public function getTrueFalse(string $inputName): ?bool
    {
        return $this->getBool($inputName);
    }
    /**
     * getJson
     * @return mixed[] or null
     */
    public function getJson(string $inputName): ?array
    {
        return $this->sharedInputFilter($inputName, "GET", "json", []);
    }
    /**
     * getArray
     * @return mixed[] or null
     */
    public function getArray(string $inputName): ?array
    {
        return $this->sharedInputFilter($inputName, "GET", "array", []);
    }
}
