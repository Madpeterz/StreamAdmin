<?php
abstract class inputFilter_base extends error_logging
{
    protected $failure = FALSE;
	protected $testOK = TRUE;
	protected $whyfailed = "";

	public function get_why_failed()
	{
		return $this->whyfailed;
	}
}
?>
