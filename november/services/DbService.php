<?php
/**
 * This file belongs to the November framework, an extension of the
 * Zend Framework, written by Marcus Nyeholt <marcus@mikenovember.com>
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to marcus@mikenovember.com so I can send you a copy immediately.
 *
 * @package   November
 * @copyright  Copyright (c) 2006-2007 Marcus Nyeholt (http://mikenovember.com)
 * @version    $Id$
 * @license    New BSD License
 */
 
include_once dirname(dirname(__FILE__)).'/exceptions/AccessDeniedException.php';
include_once dirname(__FILE__).'/DbResultSet.php';
include_once dirname(__FILE__).'/CountableSelect.php';

/**
 * A data access service that provides basic object mapping functionality
 *
 * It is not an ORM provider, as it doesn't make any assumption about how relationships
 * between objects work. You must do this yourself.
 */
class DbService implements Configurable
{
    /**
     * Where are we proxying calls to?
     *
     * @var Zend_Db_Adapter_Abstract
     */
    private $proxied;

    /**
     * Keep track of how deep in a transaction we are. If
     * a commit is called, this is checked to see whether
     * we can actually commit, otherwise it's reduced by 1
     *
     * @var int
     */
    private $transactionDepth = 0;

    /**
     * The search service
     *
     * We'll call methods against it; it's up to the application
     * to actually configure it though.
     *
     * @var SearchService
     */
    public $searchService;
    
    /**
     * The tag service might sometimes be available
     * @var TagService
     */
    public $tagService;
    
    /**
     * The item link service
     */
    public $itemLinkService;

	/**
	 * @var TypeManager
	 */
	public $typeManager;

    public function configure($config)
    {
        $this->proxied = Zend_Db::factory($config['db_type'], $config['db_params']);
    }

    /**
     * Proxies all method calls to the mapper if there's nothing defined here
     */
    public function __call($m, $a)
    {
        if (method_exists($this->proxied, $m)) {
            return call_user_func_array(array($this->proxied, $m), $a);
        }

        throw new Exception('Method '.$m.' not defined in '.get_class($this->proxied));
    }

    public function __set($k, $v)
    {
        return $this->proxied->$k = $v;
    }

    public function __get($k)
    {
        return $this->proxied->$k;
    }

	    /**
     * Creates and returns a new Zend_Db_Select object for this adapter.
     *
     * @return Zend_Db_Select
     */
    public function select()
    {
        return new CountableSelect($this->proxied);
    }

	/**
	 * Call a method on an object
	 *
	 * @param mixed $object
	 * @param string $event
	 * @return mixed
	 */
	protected function triggerObjectEvent($object, $event)
	{
		$args = func_get_args();
		array_shift($args); array_shift($args);

		if (method_exists($object, $event)) {
			return call_user_func_array(array($object, $event), $args);
		}
	}

    /**
     * Start a transaction and track how many
     * are in progress
     *
     */
    public function beginTransaction()
    {
        if ($this->transactionDepth <= 0) {
            $this->transactionDepth = 0;
            $this->proxied->beginTransaction();
        }
        $this->transactionDepth++;
        $this->log->debug("BEGIN: Transaction depth of $this->transactionDepth");
    }

    /**
     * When rolling back, reset our transaction depth
     *
     */
    public function rollback()
    {
        // If we're at depth == 0, don't need to do a rollback	
        if ($this->transactionDepth > 0) {
            $this->proxied->rollBack();
        }

        $this->log->debug("ROLLBACK");
        print_backtrace(debug_backtrace());
        $this->transactionDepth = 0;
    }

    /**
     * Decrement the transaction count. If at 0,
     * we can commit;
     *
     */
    public function commit()
    {
        $this->transactionDepth--;
        $this->log->debug("COMMIT: Transaction depth of $this->transactionDepth");
        if (!$this->transactionDepth) {
            $this->proxied->commit();
        }
        
        if ($this->transactionDepth < 0) {
            $this->transactionDepth = 0;
        }
    }

    /**
     * Gets objects of a certain class based on an array of where clauses
     *
     * @param array $where
     * @param string $order
     * @param int $page
     * @param int $number
     * @return ArrayObject
     */
    public function getObjects($type, $where=array(), $order=null, $page=null, $number=null, $authRole=null)
    {
        $select = $this->select();
        /* @var $select Zend_Db_Select */
        $select->from(strtolower($type), '*');

        $select = $this->applyWhereToSelect($where, $select);
        
        if (is_null($order)) {
            $order = mb_strtolower($type).'.id asc';
        }

        if (!is_null($page)) {
            $select->limitPage($page, $number);
        }

        $select->order($order);

        $items = $this->fetchObjects($type, $select, array(), $authRole);
        
        return $items;
    }

    /**
     * Get a count of the objects of a given where clause
     *
     * @param unknown_type $where
     */
    public function getObjectCount($where, $type)
    {
        $select = $this->select();
        $select->from(strtolower($type), new Zend_Db_Expr('count(*) as total'));
        $select = $this->applyWhereToSelect($where, $select);
        $count = $this->fetchOne($select);

        return $count;
    }
    
    /**
     * Applies the given array of where statements to the given select 
     */
    public function applyWhereToSelect($where, Zend_Db_Select $select)
    {
    	foreach ($where as $field => $value) {
        	if ($value instanceof Zend_Db_Expr && is_int($field)) {
                $select->where($value);
            } else if (is_string($field) && is_array($value)) {
            	// we have an in clause
				$in = '';
				$sep = '';
				foreach ($value as $val) {
					$in .= $sep.$this->proxied->quote($val);
					$sep = ',';
				}
				$fieldVal = new Zend_Db_Expr($field.' in ('.$in.')');
				
				$select->where($fieldVal);
            } else {
				if (strpos(mb_strtolower($field), 'or ') === 0) {
					$field = substr($field, 3);
					$select->orWhere($field.' ?', $value);
				} else {
					$select->where($field.' ?', $value);
				}
            }
        }

        return $select;
    }

    /**
     * Fetches all SQL result rows as a sequential array.
     *
     * @param Zend_Db_Select $sql An SQL SELECT statement.
     * @param array $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchObjects($class, $sql=null, $bind = array(), $authRole = null)
    {
        $tmp = new DbResultSet();
        $this->typeManager->includeType($class);
        $map = $this->typeManager->getTypeMap($class);
        
        $class = strtolower($class);
        if ($sql == null) {
            $sql = $this->select();
            $sql->from($class, '*');
        }

        if (!is_null($authRole)) {
            $itemtype = $class;
            $currentUser = za()->getUser()->getUsername();
            
            $sql->joinInner('userrole', 'userrole.itemid='.$itemtype.'.id', new Zend_Db_Expr('userrole.itemtype as userrole_itemtype'));
            // $select->joinInner($usertable, $usertable.'.username=userrole.authority', new Zend_Db_Expr('userrole.authority as userrole_authority'));
            
            $sql->where('userrole.authority = ?', $currentUser);
            $sql->where(new Zend_Db_Expr('userrole.role & '.$authRole)); 
        }
        
        $result = $this->query($sql, $bind);

        while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
            $obj = new $class();
            $obj = $this->unwrapRowToObject($obj, $row, $map);
            
            za()->inject($obj);

			$tmp[$obj->id] = $obj;
            // $tmp->append($obj);
        }

		if ($sql instanceof CountableSelect && $sql->isLimited()) {
			$countSql = $sql->getCountQuery();
			$count = $this->fetchOne($countSql);
			$tmp->setTotalResults($count);
			
		}

        return $tmp;
    }

    /**
     * Get all the values for a particular class type's attributes
     * @param String $type the class type to get the values for
     * @param string $attribute the class attribute to get the values for
     * @return array
     */
    public function getClassAttributeValues($type, $attribute)
    {
        $query = 'select distinct('.$attribute.') from '.$type;
        $result = $this->query($query);
        $res = $result->fetchAll(PDO::FETCH_COLUMN);
        return $res;
    }
    
    /**
     * Unwrap a row into an object, using the passed in typemap
     */
    private function unwrapRowToObject($obj, $row, $map)
    {
    	foreach ($row as $key => $val) {
            $property = $this->getPropertyName($key);
            // see if it's in the map, and if it has a type
            $type = ifset($map, $property);
            if ($type == 'array' || $type == 'object') {
                $val = unserialize($val);
            }

            if (method_exists($obj, 'set'.$property)) {
                $method = 'set'.$property;
                $obj->$method($val);
            } else {
                $obj->$property = $val;
            }
        }
        return $obj;
    }

    /**
     * Our own version of Statement->fetchObject so that
     * we can also account for private properties if they
     * exist in the target type
     *
     * @param Zend_Db_Statement $stmt
     * @param string $type
     */
    public function fetchObject($select, $class, $authRole = null)
    {
        $stmt = $this->query($select);
        
        $obj = new $class();
        $row = $stmt->fetch(Zend_Db::FETCH_ASSOC);
        if (!$row) return null;

        $map = $this->typeManager->getTypeMap($class);
        
		$obj = $this->unwrapRowToObject($obj, $row, $map);
        
        $stmt->closeCursor();
        za()->inject($obj);
        return $obj;
    }

    /**
     * Converts a field returned by the db to a property.
     *
     * this accounts for things like
     *
     * dbname.table.field etc.
     *
     * @param unknown_type $resultField
     */
    private function getPropertyName($resultField)
    {
        $index = strpos($resultField, '.');
        if ($index !== false) {
            $val = substr($resultField, $index+1);
            // SQLITE IS A WHORE SOMETIMES!!! >:F
            $val = trim($val, '"');
            return $val;
        }
        return $resultField;
    }

    /**
     * Get an object of the specified type
     *
     */
    public function getObject($select, $type, $authRole=null)
    {
        $this->typeManager->includeType($type);

        if (!is_null($authRole)) {
            $itemtype = strtolower($type);
            $currentUser = za()->getUser()->getUsername();
            
            $select->joinInner('userrole', 'userrole.itemid='.$itemtype.'.id', new Zend_Db_Expr('userrole.itemtype as userrole_itemtype'));
            // $select->joinInner($usertable, $usertable.'.username=userrole.authority', new Zend_Db_Expr('userrole.authority as userrole_authority'));
            
            $select->where('userrole.authority = ?', $currentUser);
            $select->where(new Zend_Db_Expr('userrole.role & '.$authRole)); 
        }
        
        
        /*@var $stmt PDOStatement */
        $obj = $this->fetchObject($select, $type, $authRole);
        
        return $obj;
    }

    /**
     * Get an object by id
     *
     * @param int $id
     */
    public function getById($id, $type, $authRole = null)
    {
        $this->typeManager->includeType($type);
        $selecttype = strtolower($type);
        $select = $this->select();
        $select->
        from($selecttype, '*')->
        where('id = ?', $id);

        $obj = $this->fetchObject($select, $type, $authRole);
        

        return $obj;
    }

    /**
     * Get an object by a given set of fields
     *
     * @param array $fields array(fieldname => fieldvalue)
     * @param string $type
     * @return object
     */
    public function getByField($fields, $type, $authRole = null)
    {
        $this->typeManager->includeType($type);
        $select = $this->select();
        $select->from(strtolower($type), '*');

        foreach ($fields as $key => $value) {
            $select->where($key . ' = ?', $value);
        }

		/* @var $select Zend_Db_Select */
		// ensures that if we have several objects, we only get the most recent
		$select->order('id desc');

        $obj = $this->fetchObject($select, $type, $authRole);

        return $obj;
    }

    /**
     * Gets many objects that match a particular set of fields
     *
     * @param map $fields
     * @param string $type
     * @return arrayobject
     */
    public function getManyByFields($fields, $type)
    {
        $this->typeManager->includeType($type);
        $select = $this->select();
        $select->from(strtolower($type), '*');

        foreach ($fields as $key => $value) {
            $select->where($key . ' = ?', $value);
        }

        return $this->fetchObjects($type, $select);
    }

    /**
     * Strips the row data out of an object, making it easier to
     * construct insert and update queries.
     *
     * @param mixed $object
     */
    public function getRowFrom($object)
    {
        $class = get_class($object);

        $fields = $this->typeManager->getTypeMap($class);

        $row = array();
        foreach ($fields as $name => $type) {
            $method = 'get'.$name;
            if (isset($object->$name) && $object->$name !== null) {
                $value = $object->$name;

                if ($type == 'array' || $type == 'object') {
                    $value = serialize($value);
                }
                $row[$name] = $value;
            }
        }

        return $row;
    }


    /**
     * Saves an object with the given parameters.
     * If the param['id'] field is filled out, then that object is loaded
     * and updated, otherwise a new object of $type is created
     *
     * @param array $object
     * @param string $type
     * @return The created object
     */
    public function saveObject($params, $type='')
    {
		$oldObject = null;
        if (is_array($params)) {
            $this->typeManager->includeType(ucfirst($type));
 
            if (isset($params['id'])) {
                $object = $this->getById((int) $params['id'], $type);
            } else {
                $object = new $type();
                za()->inject($object);
            }

            /* @var $object MappedObject */
            if ($object instanceof MappedObject) {
                $object->bind($params);
            } else {
                throw new InvalidModelException(array("Class ".get_class($object)." must subclass MappedObject to use saveObject"));
            }

            // Validate
            $modelValidator = new ModelValidator();
            if (!$modelValidator->isValid($object)) {
                throw new InvalidModelException($modelValidator->getMessages());
            }
        } else {
            $object = $params;
        }

        if ($object == null) throw new Exception("Cannot save null object");
        
        $refObj = new ReflectionObject($object);
        if ($refObj->hasProperty('updated')) {
            $object->updated = date('Y-m-d H:i:s', time());
        }
		if ($refObj->hasProperty('modifier')) {
            $object->modifier = za()->getUser()->getUsername();
        }

        if (get_class($object) == 'stdClass') {
            throw new Exception("Cannot save stdClass");
        }

        $success = false;

		$this->triggerObjectEvent($object, 'beforeSave');

        if ($object->id) {
            $success = $this->updateObject($object);
        } else {
            $success = $this->createObject($object);
        }

		$this->triggerObjectEvent($object, 'saved');
		
        if ($success) {
			if ($this->searchService != null) {
				$this->searchService->index($object);
			}

	        return $object;
        } 
        
        return null;
    }

    /**
     * Create a row for a given object.
     * forwards the call to the underlying insert() method.
     *
     * @param unknown_type $object
     */
    public function updateObject($object, $where='')
    {
        if ($where == '') {
            $where = $this->quoteInto('id = ?', $object->id);
        }

        $table = strtolower(get_class($object));
        $row = $this->getRowFrom($object);

		$this->triggerObjectEvent($object, 'update');
        $ret = $this->update($table, $row, $where);

		return $ret;
    }

    /**
     * Create a row for a given object.
     * forwards the call to the underlying insert() method.
     *
     * @param mixed $object
     * @param boolean $assignId Whether to assign the generated ID back as
     *                     the object's ID.
     */
    public function createObject($object, $assignId=true)
    {
        $table = strtolower(get_class($object));

        // set some properties
        $refObj = new ReflectionObject($object);
        if ($refObj->hasProperty('created') && !$object->created) {
            $object->created = date('Y-m-d H:i:s', time());
        }

        if ($refObj->hasProperty('creator') && !$object->creator) {
            $object->creator = za()->getUser()->getUsername();
        }
        $row = $this->getRowFrom($object);
        try {
			$this->triggerObjectEvent($object, 'create');
            $return = $this->insert($table, $row);
        	
            if ($return && $assignId) {
                // Return the last insert ID
                $object->id = $this->proxied->lastInsertId($table, 'id');
				$this->triggerObjectEvent($object, 'created');
	            
                return $object->id;
            }
			$this->triggerObjectEvent($object, 'created');
            return $return;
        } catch (Exception $e) {
            error_log("Caught: ".$e->getMessage());
            error_log($e->getTraceAsString());
            throw $e;
        }
        return false;
    }

    /**
     * Proxy the call to delete
     *
     * @param  string|object $table
     * @param string $where
     */
    public function delete($table, $where = '')
    {
        if (is_object($table)) {
            $where = 'id = '.$table->id;
            if ($this->searchService != null) {
                $this->searchService->delete($table);
            }
            if ($this->tagService != null) {
                $this->tagService->deleteTags($table, za()->getUser());
            }
            if ($this->itemLinkService != null) {
                $this->itemLinkService->deleteItem($table);
            }

            $table = get_class($table);
        }
        $table = strtolower($table);
        $return = false;
        try {
            $this->beginTransaction();
            if (is_array($where)) {
                $where = $this->bindValues($where);
            }
            $this->proxied->delete($table, $where);
            $return = true;
            $this->commit();
        } catch (Exception $e) {
            error_log(get_class($e)." - Caught: ".$e->getMessage());
            error_log($e->getTraceAsString());
            $this->rollBack();
            throw $e;
        }
        return $return;
    }

    /**
     * Bind a list of statement=? => value arrays
     *
     * @param unknown_type $bind
     */
    public function bindValues($bind)
    {
        $set = array();
        foreach ($bind as $col => $val) {
            if ($val instanceof Zend_Db_Expr) {
                $val = $val->__toString();
                unset($bind[$col]);
            }
            if (is_string($col)) {
                $col = $this->quoteInto($col, $val);
            }
            $set[] = $col;
        }
        return $set;
    }
}
?>
