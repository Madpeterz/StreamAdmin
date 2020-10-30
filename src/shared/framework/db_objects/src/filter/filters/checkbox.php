<?php
abstract class inputFilter_filter_checkbox extends inputFilter_filter_vector
{
    protected function filter_checkbox(string $value,array $args=[])
    {
        $filter_as = "integer";
        $this->failure=TRUE;
        $this->testOK=FALSE;
        if(is_array($args) == true)
        {
            if(count($args) > 0)
            {
                if(array_key_exists("filter",$args) == true)
                {
                    $filter_as = $args["filter"];
                }
            }
        }
        $filter_as = "filter_".$filter_as;
        if($filter_as != "filter_checkbox")
        {
            if(method_exists($this,$filter_as) == true)
            {
                return $this->$filter_as($value,$args);
            }
            $this->whyfailed="Unable to find filter to use";
            return null;
        }
        $this->whyfailed="filter self loop detected";
        return null;
    }
}
?>
