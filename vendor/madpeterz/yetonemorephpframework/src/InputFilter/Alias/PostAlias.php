<?php

namespace YAPF\InputFilter\Alias;

use YAPF\InputFilter\Worker\InputFilterWorkerPost;

class PostAlias extends GetAlias
{
    public function postUUID(string $inputName): ?string
    {
        return $this->sharedInputFilter($inputName, $_POST, "uuid", []);
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
        return $this->sharedInputFilter($inputName, $_POST, "string", $args);
    }
    public function postInteger(string $inputName, bool $zeroCheck = false, bool $greaterThanZero = false): ?int
    {
        $args  = [];
        if ($zeroCheck != false) {
            $args["zeroCheck"] = "Enabled";
        } elseif ($greaterThanZero != false) {
            $args["gtr0"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, $_POST, "integer", $args);
    }
    public function postFloat(string $inputName, bool $zeroCheck = false): ?float
    {
        $args  = [];
        if ($zeroCheck != false) {
            $args["zeroCheck"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, $_POST, "float", $args);
    }
    public function postCheckbox(string $inputName, string $useFilter = "integer"): ?float
    {
        $args  = ["filter" => $useFilter];
        return $this->sharedInputFilter($inputName, $_POST, "checkbox", $args);
    }
    public function postBool(string $inputName): ?bool
    {
        return $this->sharedInputFilter($inputName, $_POST, "bool", []);
    }
    public function postVector(string $inputName, bool $strictChecks = false): ?string
    {
        $args = [];
        if ($strictChecks == true) {
            $args["stricstrictt"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, $_POST, "vector", $args);
    }
    public function postDate(string $inputName, bool $asUNIX = false, bool $humanReadable = false): ?string
    {
        $args = [];
        if ($asUNIX == true) {
            $args["asUNIX"] = "Enabled";
        } elseif ($humanReadable == true) {
            $args["humanReadable"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, $_POST, "date", $args);
    }
    public function postEmail(string $inputName, bool $noMailboxs = false): ?string
    {
        $args = [];
        if ($noMailboxs == true) {
            $args["no_mailboxs"] = "no_mailboxs";
        }
        return $this->sharedInputFilter($inputName, $_POST, "email", $args);
    }
    public function postUrl(string $inputName, bool $isHTTPS = false, bool $isHTTP = false): ?string
    {
        $args = [];
        if ($isHTTPS == true) {
            $args["isHTTPS"] = "Enabled";
        } elseif ($isHTTP == true) {
            $args["isHTTP"] = "Enabled";
        }
        return $this->sharedInputFilter($inputName, $_POST, "url", $args);
    }
    /**
     * getColour
     * @return mixed or mixed[] or null
     */
    public function postColour(string $inputName, bool $isHEX = false, bool $isRGB = false): ?string
    {
        return $this->getColor($inputName, $isHEX, $isRGB);
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
        return $this->sharedInputFilter($inputName, $_POST, "color", $args);
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
        return $this->sharedInputFilter($inputName, $_POST, "json", []);
    }
    /**
     * getArray
     * @return mixed[] or null
     */
    public function postArray(string $inputName): ?array
    {
        return $this->sharedInputFilter($inputName, $_POST, "array", []);
    }
}
