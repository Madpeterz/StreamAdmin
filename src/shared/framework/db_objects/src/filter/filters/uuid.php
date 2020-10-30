<?php
abstract class inputFilter_filter_uuid extends inputFilter_filter_bool
{
    protected function filter_uuid(string $value,array $args=[]) : ?string
	{
        $this->failure = FALSE;
        $this->testOK = TRUE;
        if(preg_match('/^[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-4[0-9A-Fa-f]{3}\-[89ABab][0-9A-Fa-f]{3}\-[0-9A-Fa-f]{12}$/i', $value)) return $value;
        else if(preg_match('/^[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}$/i', $value)) return $value;
        return null;
	}
}
?>
