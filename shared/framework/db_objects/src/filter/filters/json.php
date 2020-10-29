<?php
abstract class inputFilter_filter_json extends inputFilter_filter_uuid
{
    protected function filter_json(string $value) : ?string
    {
        $json = json_decode($value, true);
        if(($json === false) || ($json === null)) return null;
        else return $json;
    }
}
?>
