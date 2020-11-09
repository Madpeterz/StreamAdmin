<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeColor extends InputFilterTypeCheckbox
{
    protected function colorSupportIsHex(string $value): ?string
    {
        if (preg_match('/^#[a-f0-9]{6}$/i', $value)) {
            return $value;
        } elseif (preg_match('/^[a-f0-9]{6}$/i', $value)) {
            return $value;
        }
        $this->whyfailed = "value did not match any IsHex rules";
        return null;
    }

    protected function colorSupportLSLVector(string $value, float $maxvalue = 1): ?string
    {
        $testLSL = $this->filterVector($value);
        if ($testLSL == null) {
            return null;
        }
        $vectorTest = explode(",", str_replace(["<", " ", ">"], "", $testLSL));
        $tests = [];
        $tests[] = $this->valueInRange(0, $maxvalue, $vectorTest[0]); // R
        $tests[] = $this->valueInRange(0, $maxvalue, $vectorTest[1]); // G
        $tests[] = $this->valueInRange(0, $maxvalue, $vectorTest[2]); // B
        if (in_array(false, $tests) == true) {
            $this->whyfailed = "one or more values are out of spec";
            return null;
        }
        return $value;
    }



    protected function colorSupportConvert(string $value, array $args = []): ?string
    {
        if ((function_exists("rgb2hex") == false) || (function_exists("hex2rgb") == false)) {
            $this->whyfailed = "Convertor functions are missing";
            return null;
        }
        if (array_key_exists("hex", $args) == true) {
            $result = $this->colorSupportLSLVector($value);
            if ($result != null) {
                $result = $this->scaleVector($result);
                return rgb2hex($result);
            }
            $result = $this->colorSupportLSLVector($value, 255);
            if ($result != null) {
                $result = $this->scaleVector(0);
                return rgb2hex($result);
            }
        } elseif (array_key_exists("lsl", $args)) {
            $result = $this->colorSupportIsHex($value);
            if ($result != null) {
                $rgb = hex2rgb($value);
                $rgb[0] /= 255;
                $rgb[1] /= 255;
                $rgb[2] /= 255;
                return "<" . implode(',', $rgb) . ">";
            }
        } elseif (array_key_exists("rgb", $args)) {
            $result = $this->colorSupportIsHex($value);
            if ($result != null) {
                return hex2rgb($value);
            }
            $result = $this->colorSupportLSLVector($value);
            if ($result != null) {
                $result = $this->scaleVector($result);
                return rgb2hex($result);
            }
        }
        return null;
    }

    /**
     * filterColor
     * Does stuff not sure what blame shado.
     * @return mixed or mixed[] or null
     */
    protected function filterColor(string $value, array $args = [])
    {
        // default is LSL, supply a isXXX rule to switch
        if (array_key_exists("isHEX", $args)) {
            $value = $this->colorSupportIsHex($value);
        } elseif (array_key_exists("isRGB", $args)) {
            $value = $this->colorSupportLSLVector($value, 255);
        } else {
            $value = $this->colorSupportLSLVector($value);
        }
        if (array_key_exists("Convert", $args)) {
            $value = $this->colorSupportConvert($value, $args);
        }
        return $value;
    }
}
