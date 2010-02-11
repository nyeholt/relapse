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
		// make sure that there's a versioning table available for this object
		$versionType = get_class($object).'Version';
		if (!class_exists($versionType)) {
			throw new Exception("Attempting to create a version for a non-versionable object");
		}

		if (!$object) {
			throw new Exception("Cannot create a version of an empty object");
		}
		// see if there's a previous object version, if so load it
		// so we have an appropriate 'from' time
		$lastVersion = $this->getMostRecentVersion($object);
		$from = date('Y-m-d H:i:s');
		if ($lastVersion) {
			$from = date('Y-m-d H:i:s', strtotime($lastVersion->created) + 1);
		}

		$newVersion = new $versionType();
		$properties = $object->unBind();

		unset($properties['id']);
		unset($properties['created']);
		unset($properties['creator']);

		$newVersion->bind($properties);

		$newVersion->recordid = $object->id;
		$newVersion->validfrom = $from;
		$newVersion->label = $label;

		return $this->dbService->saveObject($newVersion);
	}

	/**
	 * Gets the most recent version of a particular object
	 *
	 * @param object $object
	 */
	public function getMostRecentVersion($object)
	{
		$table = get_class($object).'Version';
		return $this->dbService->getByField(array('recordid' => $object->id), $table);
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
	public function getVersionsFor($object, $from=null, $to=null, $filter = array())
	{
		$type = null;

		if (is_object($object) && (!is_array($object) || !$object instanceof ArrayAccess)) {
			$object = array($object);
		} else if (is_string($object)) {
			$type = $object;
		}

		$ids = array();

		if (!$type) {
			foreach ($object as $obj) {
				$ids[] = $obj->id;
				$type = get_class($obj);
			}
		}

		$table = mb_strtolower($type.'Version');
		$select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
		$select->from($table, '*');
		
		if ($from) {
			// From means versions that were created after this date
			$select->where('created > ?', $from);
			// $select->where('validfrom > ?', $from);
		}
		if ($to) {
			$select->where('validfrom < ?', $to);
		}

		if (count($ids)) {
			$filter['recordid'] = $ids;
		}

		$select->order('id DESC');
		$this->dbService->applyWhereToSelect($filter, $select);

		$versions = $this->dbService->fetchObjects($type.'Version', $select);

		return $versions;
	}

	/**
	 * Gets a collection of objects (not versions, but the raw objects) that
	 * existed at a particular point in time. If there's nothing in the version
	 * history, the current state object is used if it was created before the
	 * 'to' date
	 *
	 * @param mixed $objects
	 * @param datetime $from
	 * @param datetime $to
	 * @param string $label
	 */
	public function getVersionedObjectsAt($objects, $from=null, $to=null, $filter = array())
	{
		$versions = $this->getVersionsFor($objects, $from, $to, $filter);

		// now that we have all the versions, we need to go through and create
		// a collection of the actual items, and make sure that if there's 'no'
		// version, that we include the current object if it's acceptable

		// if $objects is a collection, then we need to see if there are any
		// objects in there that fit in the 'from' and 'to' if they weren't
		// found in the list of versions
		if (is_object($objects) && (!is_array($objects) || !$objects instanceof ArrayAccess)) {
			$objects = array($objects->id => $objects);

			foreach ($objects as $object) {
				$has = false;
				foreach ($versions as $v) {
					if ($v->recordid == $object->id) {
						$has = true;
						break;
					}
				}

				if (!$has) {
					// if the current object's created date is less than the 'to'
					// date, then it's safe to use as it existed in the range
					$created = strtotime($object->created);
					$toStr = strtotime($to);
					if ($created < $to) {
						$versions[$object->id] = $object;
					}
				}
			}
		} else if (is_string($objects)) {
			// else we need to see if there are any objects that exist in the 'live'
			// tables that have existed before the 'to' date
		}


		
		
	}
}
?>