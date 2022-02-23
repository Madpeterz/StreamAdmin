<?php

namespace YAPF\InputFilter;

use YAPF\Core\ErrorControl\ErrorLogging as ErrorLogging;

abstract class Base extends ErrorLogging
{
    protected string $whyfailed = "";
    protected ?string $valueAsString = null;
    protected ?int $valueAsInt = null;
    protected ?float $valueAsFloat = null;
    protected bool $valueAsBool = false;
    protected ?array $valueAsArray = null;
    protected ?string $valueAsHumanReadable = null;


    protected function reset(): void
    {
        $this->whyfailed = "in reset mode";
        $this->valueAsBool = false;
        $this->valueAsInt = null;
        $this->valueAsString = null;
        $this->valueAsFloat = null;
        $this->valueAsArray = null;
        $this->valueAsHumanReadable = null;
    }


    /**
     * whyFailed
     * returns the last stored fail message
     */
    public function getWhyFailed(): string
    {
        return $this->whyfailed;
    }

    protected function failed(string $why): void
    {
        $this->reset();
        $this->addError(__FILE__, __FUNCTION__, $why);
        $this->whyfailed = $why;
    }

    public function post(string $field): InputFilter
    {
        $this->reset();
        if (array_key_exists($field, $_POST) == false) {
            return $this;
        }
        $this->convertValue("post", $field);
        return $this;
    }
    public function get(string $field): InputFilter
    {
        $this->reset();
        if (array_key_exists($field, $_GET) == false) {
            return $this;
        }
        $this->convertValue("get", $field);
        return $this;
    }
    public function varinput(?string $value): InputFilter
    {
        $this->reset();
        $this->convertValue("", "", $value);
        return $this;
    }

    /**
     * fetchValue
     * @return mixed string|array|null
     */
    protected function fetchValue(string $source, string $field)
    {
        $sourceDat = [];
        if ($source == "post") {
            $sourceDat = &$_POST;
        } elseif ($source == "get") {
            $sourceDat = &$_GET;
        }
        if (array_key_exists($field, $sourceDat) == false) {
            return null;
        }
        return $sourceDat[$field];
    }

    protected function convertValue(string $source, string $field, ?string $value = null): void
    {
        if ($value == null) {
            $value = $this->fetchValue($source, $field);
        }
        $this->valueAsArray = null;
        if (is_array($value) == true) {
            $this->valueAsArray = $value;
            $value = json_encode($value);
        }
        $this->valueAsString = htmlentities($value);
        $this->valueAsInt = intval($this->valueAsString);
        $this->valueAsFloat = floatval($this->valueAsString);
        $this->valueAsBool = in_array(
            $this->valueAsString,
            ["1","true",true,1,"yes","True","TRUE"],
            true
        );
    }
}
