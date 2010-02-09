<?php
/*

Copyright (c) 2006-2007, Marcus Nyeholt
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software
      without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
OF SUCH DAMAGE.
*/

/**
 * A versioning service to store serialised representations of versioned objects
 * 
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class VersioningService
{
    
	/**
	 *
	 * @var DbService
	 */
	public $dbService;

	/**
	 * Creates a new version of the passed in object
	 * 
	 * @param object $object
	 * @return ObjectVersion
	 */
	public function createVersion($object, $label='')
	{
		if (!$object) {
			throw new Exception("Cannot create a version of an empty object");
		}
		// see if there's a previous object version, if so load it
		// so we have an appropriate 'from' time
		$lastVersion = $this->dbService->getByField(array('objectid' => $object->id, 'objecttype' => get_class($object)), 'ObjectVersion');
		$from = '2000-01-01 00:00:01';
		if ($lastVersion) {
			$from = $lastVersion->created;
		}

		$newVersion = new ObjectVersion();
		$newVersion->objectid = $object->id;
		$newVersion->objecttype = get_class($object);
		$newVersion->validfrom = $from;
		$newVersion->item = $object; // this gets serialised automatically
		$newVersion->label = $label;

		return $this->dbService->saveObject($newVersion);
	}

	/**
	 * Get a list of versions of objects for the passed in objects.
	 *
	 * This is a useful way of getting a complete history of the state of objects
	 * at a particular time. To get a snapshot of the 'valid' objects for
	 * a given time, use the 'getVersionsAt' method
	 *
	 * @param ArrayObject $objects
	 */
	public function getVersionsFor($object, $from=null, $to=null, $label = '')
	{
		if (!is_array($object) || !$object instanceof ArrayAccess) {
			$object = array($object);
		}

		$ids = array();
		$type = '';

		foreach ($object as $obj) {
			$ids[] = $obj->id;
			$type = get_class($obj);
		}

		$select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
		$select->from('objectversion', '*')->where('objecttype = ?', $type);
		
		if ($from) {
			// From means versions that were created after this date
			$select->where('created > ?', $from);

			// $select->where('validfrom > ?', $from);
		}
		if ($to) {
			$select->where('validfrom < ?', $to);
		}
        if ($label) {
			$select->where('label = ?', $label);
		}

		$this->dbService->applyWhereToSelect(array('objectid' => $ids), $select);

		return $this->dbService->fetchObjects('ObjectVersion', $select);
	}
}
?>