<?php

/**
 * @author Marcus Nyeholt <nyeholt@gmail.com>
 */
class DbResultSet extends ArrayObject
{
    protected $totalResults;

	public function setTotalResults($v)
	{
		$this->totalResults = $v;
	}

	public function getTotalResults()
	{
		return $this->totalResults;
	}
}
?>