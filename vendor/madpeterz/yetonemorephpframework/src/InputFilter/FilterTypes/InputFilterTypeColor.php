<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeColor extends InputFilterTypeCheckbox
{
    /**
     * filterColor
     * Does stuff not sure what blame shado.
     */
    protected function filterColor(string $value, array $args = []): ?string
    {
        $this->failure = false;
        $this->testOK = true;
        if ((function_exists("rgb2hex") == true) && (function_exists("hex2rgb") == true)) {
            if (array_key_exists("convert", $args)) {
                if (array_key_exists("hex", $args)) {
                    if ($this->postFilter($value, "color", "isLSL") != null) {
                        $vectorTest = explode(",", str_replace(["<", " ", ">"], "", $value));
                        $vectorTest[0] *= 255;
                        $vectorTest[1] *= 255;
                        $vectorTest[2] *= 255;
                        return rgb2hex($vectorTest);
                    }
                    if ($this->postFilter($value, "color", "isRGB") != null) {
                        $vectorTest = explode(",", str_replace(["<", " ", ">"], "", $value));
                        return rgb2hex($vectorTest);
                    } else {
                        return $value;
                    }
                }
                if (array_key_exists("lsl", $args)) {
                    if ($this->postFilter($value, "color", "isHEX") != null) {
                        $rgb = hex2rgb($value);
                        $rgb[0] /= 255;
                        $rgb[1] /= 255;
                        $rgb[2] /= 255;
                        return "<" . implode(',', $rgb) . ">";
                    }
                    if ($this->postFilter($value, "color", "isRGB") != null) {
                        $rgb = explode(",", str_replace(["<", " ", ">"], "", $value));
                        $rgb[0] /= 255;
                        $rgb[1] /= 255;
                        $rgb[2] /= 255;
                        return "<" . implode(',', $rgb) . ">";
                    }
                }
                if (array_key_exists("rgb", $args)) {
                    if ($this->postFilter($value, "color", "isHEX") != null) {
                        return hex2rgb($value);
                    }
                    if ($this->postFilter($value, "color", "isLSL") != null) {
                        $lsl = explode(",", str_replace(["<", " ", ">"], "", $value));
                        $lsl[0] *= 255;
                        $lsl[1] *= 255;
                        $lsl[2] *= 255;
                        return implode(',', $lsl);
                    }
                }
            }
        }
        if (array_key_exists("isHEX", $args)) {
            if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
                return $value;
            } elseif (preg_match('/^[a-f0-9]{6}$/i', $color)) {
                return $value;
            }
        }
        if (array_key_exists("isLSL", $args)) {
            $testLSL = $this->filter_vector($value);
            if ($testLSL == null) {
                $this->testOK = false;
            } else {
                $vectorTest = explode(",", str_replace(["<", " ", ">"], "", $testLSL));
                if (
                    ($vectorTest[0] < 0) ||
                    ($vectorTest[0] > 1) ||
                    ($vectorTest[1] < 0) ||
                    ($vectorTest[1] > 1) ||
                    ($vectorTest[2] < 0) ||
                    ($vectorTest[2] > 1)
                ) {
                    $this->testOK = false;
                } else {
                    return $value;
                }
            }
        }
        if (array_key_exists("isRGB", $args)) {
            $testRGB = $this->filter_vector($value);
            if ($testRGB == null) {
                $this->testOK = false;
            } else {
                $vectorTest = explode(",", str_replace(["<", " ", ">"], "", $testLSL));
                if (
                    ($vectorTest[0] < 0) ||
                    ($vectorTest[0] > 255) ||
                    ($vectorTest[1] < 0) ||
                    ($vectorTest[1] > 255) ||
                    ($vectorTest[2] < 0) ||
                    ($vectorTest[2] > 255)
                ) {
                    $this->testOK = false;
                } else {
                    return $value;
                }
            }
        }
        if ($this->testOK) {
            return $value;
        } else {
            return null;
        }
    }
}
