<?php

namespace YAPF\InputFilter\FilterTypes;

abstract class InputFilterTypeUUID extends InputFilterTypeBool
{
    /**
     * filterUUID
     * checks to see if the input is a vaild UUID
     * note: supports multiple specs.
     */
    protected function filterUUID(string $value, array $args = []): ?string
    {
        $this->failure = false;
        $this->testOK = true;
        $uuid_specs = [
            '/^[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-4[0-9A-Fa-f]{3}\-[89ABab][0-9A-Fa-f]{3}\-[0-9A-Fa-f]{12}$/i',
            '/^[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}$/i',
        ];
        foreach ($uuid_specs as $spec) {
            if (preg_match($spec, $value)) {
                return $value;
            }
        }
        return null;
    }
}
