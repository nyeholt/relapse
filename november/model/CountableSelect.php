<?php

/**
 * A custom select statement class that has additional methods to return an SQL fragment
 * that provides convenience methods for building a query to return a count of results.
 * 
 * @author Marcus Nyeholt <nyeholt@gmail.com>
 */
class CountableSelect extends Zend_Db_Select
{
	/**
	 * Indicates whether this query is limited or not. 
	 * 
	 * If it is, then it's likely that the user wants to get a 'count' query. 
	 * 
	 */
	public function isLimited()
	{
		return isset($this->_parts[Zend_Db_Select::LIMIT_COUNT]);
	}

	/**
	 * Create the query that could be used for counting instead of
	 * raw data returns. 
	 */
    public function getCountQuery()
	{
        $sql = self::SQL_SELECT;
		
        foreach (array_keys(parent::$_partsInit) as $part) {
			if ($part == Zend_Db_Select::ORDER ||  $part == Zend_Db_Select::LIMIT_COUNT || $part == Zend_Db_Select::LIMIT_OFFSET) {
				continue;
			}

			if ($part == Zend_Db_Select::COLUMNS) {
				$part = 'CountColumn';
			}

			$method = '_render' . ucfirst($part);
			if (method_exists($this, $method)) {
				$sql = $this->$method($sql);
			}
		}

		return $sql;
	}

	/**
	 * Renders the 'count' of objects for the column
	 *
	 * @param String $sql
	 */
	protected function _renderCountColumn($sql)
	{
//		foreach ($this->_parts[self::FROM] as $correlationName => $table) {
//			return $sql .= ' count('.$correlationName.'.id) ';
//		}
		return $sql .= ' count(*)';
	}
}
?>