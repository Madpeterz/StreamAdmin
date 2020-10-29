<?php
abstract class inputFilter_postFilter extends inputFilter_getFilter
{
    public function postFilter(string $inputName,string $filter="string",array $args = array(), $default = null)
	{
		$this->failure = false;
		$this->whyfailed = "";
		$value = $default;
		if(isset($_POST[$inputName]) == false)
		{
            $this->failure = true;
		}
        if($this->failure == false)
        {
            $value = $this->valueFilter($_POST[$inputName], $filter, $args);
            if($this->whyfailed != "")
            {
                $this->addError(__FILE__,__FUNCTION__,$this->whyfailed);
            }
        }
		return $value;
	}
}
?>
