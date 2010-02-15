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
 * Versions are stored using a 'validfrom'; this date range
 * 'validfrom' -> 'versioncreated' for the version indicates when this version
 * represented the 'live' state for the object. So the 'versioncreated' time of the
 * version doesn't represent when this version was first versioncreated from a 'live'
 * perspective, but when the version was taken (ie the time the new object
 * became the 'live' representation)
 *
 * Note that for a 'version', the date the version was created is 'versioncreated'
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
	 * @param MappedObject $object
	 * @return ObjectVersion
	 */
	public function createVersion(MappedObject $object, $label='')
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
			$from = date('Y-m-d H:i:s', strtotime($lastVersion->versioncreated) + 1);
		}

		$newVersion = new $versionType();
		// when creating a version, we want to load the existing state from
		// the DB so that we're being accurate.
		$current = $this->dbService->getById($object->me()->id, get_class($object));
		$properties = $current->unBind();

		unset($properties['id']);
		
		/*unset($properties['created']);
		unset($properties['creator']);*/

		$newVersion->bind($properties);

		$newVersion->recordid = $object->id;
		$newVersion->versioncreated = date('Y-m-d H:i:s');
		$newVersion->validfrom = $from;
		$newVersion->label = $label;

		return $this->dbService->saveObject($newVersion);
	}

	/**
	 * Gets the most recent version of a particular object
	 *
	 * @param object $object
	 */
	public function getMostRecentVersion(MappedObject $object)
	{
		$table = get_class($object).'Version';
		return $this->dbService->getByField(array('recordid' => $object->me()->id), $table);
	}

	/**
	 * Get a list of versions of objects for the passed in objects.
	 *
	 * This is a useful way of getting a complete history of the state of objects
	 * at a particular time. To get a snapshot of the 'valid' objects for
	 * a given time, use the 'getVersionedObjects' method
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
			$select->where('versioncreated > ?', $from);
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
	 * Gets a collection of objects that existed at a particular point in time
	 *
	 * @param mixed $objects
	 * @param datetime $from
	 * @param datetime $to
	 * @param string $label
	 */
	public function getObjectSnapshot($type, $date, $filter = array())
	{
		$versionType = $type.'Version';

		$filter = array();

		$filter['validfrom < '] = $date;
		$filter['versioncreated > '] = $date;
		
		$versions = $this->dbService->getObjects($versionType, $filter);

		// because we're after a snapshot, it might be the case that there
		// are some objects that are 'live' that haven't had a version created
		// in the period we're interested in. So we're looking for objects
		// that were created BEFORE the date we're interested in, but don't
		// have a date in the 'valid' range

		$ids = array();
		foreach ($versions as $v) {
			$ids[] = $v->recordid;
		}

		$filter = array('versioncreated < ' => $date);
		if (count($ids)) {
			$filter['id NOT'] = $ids;
		}

		$live = $this->dbService->getObjects($type, $filter);

		foreach ($live as $liveObject) {
			$liveObject->recordid = $liveObject->id;
			$versions[] = $liveObject;
		}
		return $versions;
	}
}
?>