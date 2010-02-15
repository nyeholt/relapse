<?php

class ItemLinkService
{
    /**
     * The DbService
     *
     * @var DbService
     */
    public $dbService;
    
    /**
     * Create a new item based on an existing one
     * 
     * $params is an array of the form
     * array(
     *  'id' => the ID of the object being created from
     *  'type' => the type of the object this new object is being created from
     *  'createtype' => The type of the new object to create
     * )
     */
    public function createNewItem($params)
    {
        $from = $this->sanitizeType($params['type']);
        if (!isset($params['type'])) {
            throw new Exception("Invalid params: ".print_r($params, true));
        }
        $to = $this->sanitizeType($params['createtype']);
        
        $this->dbService->beginTransaction();
        
            $createFrom = $this->dbService->getById($params['id'], $params['type']);
            if (!$createFrom) {
                throw new Exception("Create from object is not valid");
            }
            
            $method = 'create'.$to.'From'.$from;
            $obj = null;
            if (method_exists($this, $method)) {
                $obj = $this->$method($params, $createFrom);
            } else {
                $obj = $this->defaultCreate($params, $createFrom);
            }
    
            $this->parentChildLink($createFrom, $obj);
        
        $this->dbService->commit();
        
        return $obj;
    }
    
    public function parentChildLink($fromItem, $toItem) {
        $existing = $this->getLinkBetween($fromItem, $toItem);
        if ($existing) {
            throw new Exception("A link already exists between these items");
        }
        
        if ($fromItem->id == $toItem->id && get_class($fromItem) == get_class($toItem)) {
            throw new RecursiveLinkException("Cannot link an item to itself.");            
        }
        $this->log->debug("Linking #".$fromItem->id.' to #'.$toItem->id);

        // Now go through all the parents of $toItem to make sure that from
        // item isn't already a parent of it
        $ancestors = $this->getAncestors($toItem);
        foreach ($ancestors as $ancestor) {
            // if its id and type matches, then we're introuble
            if ($ancestor->id == $fromItem->id && get_class($fromItem) == get_class($ancestor)) {
                throw new RecursiveLinkException(get_class($fromItem)." #{$fromItem->id} is already an ancestor of this item");
            }
        }
        
        $ancestors = $this->getAncestors($fromItem);
	    foreach ($ancestors as $ancestor) {
            // if its id and type matches, then we're introuble
            if ($ancestor->id == $toItem->id && get_class($toItem) == get_class($ancestor)) {
                throw new RecursiveLinkException(get_class($toItem)." #{$toItem->id} is already an ancestor of this item");
            }
        }
        return $this->linkItems($fromItem, $toItem);
    }
    
    /**
     * Link two different items together.
     *
     * @param object $fromItem
     * @param object $toItem
     */
    public function linkItems($fromItem, $toItem)
    {
        $link = array( 
            'fromid' => $fromItem->id,
            'fromtype' => get_class($fromItem),
            'toid' => $toItem->id,
            'totype' => get_class($toItem),
        );

        return $this->dbService->saveObject($link, 'ItemLink');
    }
    
    /**
     * Gets all ancestors of an item
     */
    public function getAncestors($item)
    {
        // get all the items that link TO $item
        $items = $this->getLinkedItems($item, 'to');
        
        $out = new ArrayObject();
        $out = $items;
        foreach ($items as $parent) {
            $grandParents = $this->getAncestors($parent);
            foreach ($grandParents as $gp) {
                $out[] = $gp;
            }
        }

        return $out;
    }

    /**
     * Retrieve the link between two items
     */
    public function getItemLink($id)
    {
        return $this->dbService->getById($id, 'ItemLink');
    }

    /**
     * Get an existing link between two items
     *
     * @param object $item1
     * @param object $item2
     */
    public function getLinkBetween($item1, $item2)
    {
        $where = array(
            'fromid=' => $item1->id,
            'fromtype=' => get_class($item1),
            'toid=' => $item2->id,
            'totype=' => get_class($item2),
        );

        $results = $this->dbService->getObjects('ItemLink', $where);
        
        return count($results) ? current($results) : null;
    }

    /**
     * Deletes the link between two items
     */
    public function deleteLinkBetween($from, $to)
    {
        $link = $this->getLinkBetween($from, $to);
        if ($link == null) {
            throw new Exception("No link found from ".$from->title." to ".$to->title);
        }
        $this->dbService->delete($link);
        return;
    }

    /**
     * Gets all the items this passed in item is linked to
     * 
     * if $side is 'from', it will return items where
     * this item is the 'from' side. If 'to', will return
     * the 'to' side, otherwise if 'both' will return 
     * both sides.
     *
     * @param  object $item
     * @param  string $side The "side" that $item is linked on. EG left side = 'from'
     */
    public function getLinkedItems($item, $side='from', $otherType=null)
    {
        $results = null;
        $finalResults = new ArrayObject();
        if ($otherType) {
            return $this->getLinkedItemsOfType($item, $side, $otherType);
        }
        if ($side == 'from' || $side == 'both') {
            $where = array(
                'fromid=' => $item->id,
                'fromtype=' => get_class($item),
            );
            
            if ($otherType != null) {
                $where['totype='] = $otherType;
            }
            
            $results = $this->dbService->getObjects('ItemLink', $where);
            
            foreach ($results as $linkItem) {
                $object = $this->dbService->getById($linkItem->toid, $linkItem->totype);
                if ($object) {
                	$finalResults[] = $object;
                }
            }
        } 
        
        if ($side == 'to' || $side == 'both') {
            $where = array(
                'toid=' => $item->id,
                'totype=' => get_class($item),
            );
            
            if ($otherType != null) {
                $where['fromtype='] = $otherType;
            }
            
            $results = $this->dbService->getObjects('ItemLink', $where);
            
            foreach ($results as $linkItem) {
                $object = $this->dbService->getById($linkItem->fromid, $linkItem->fromtype);
                $finalResults[] = $object;
            }
        }
        
        return $finalResults;
    }
    
    /**
     * Gets linked items of a particular type. Used when you know that 
     * the linked item is definitely of a certain type, means that a quicker
     * query can be used, as well as sorting. 
     * 
     * @param mixed $item The item to get the links for
     * @param String $side Which side are the items to fetch compared to the $item? 
     * @param String $type the type of objects to be getting
     * @param array $where an optional where clause to apply
     */
    public function getLinkedItemsOfType($item, $side='from', $type, $where = array(), $sortby='id asc')
    {
        if ($item == null) {
            $this->log->warn("Tried getting linked items of null object");
            return new ArrayObject();
        }

        $results = null;
        $finalResults = new ArrayObject();
        
        /* @var $select Zend_Db_Select */
        $select = $this->dbService->select();
        $tableName = mb_strtolower($this->sanitizeType($type));
        $itemType = get_class($item);
        // $tableName = $this->dbService->quote(mb_strtolower($type));
        // select * from $type left join itemlink on itemlink.type = $type and itemlink.to=$type.id where itemlink.to is null 
        if ($side == 'from') {
	        $select->from($tableName)
	            ->joinLeft('itemlink', 'itemlink.totype = '.$this->dbService->quote($type).' and itemlink.toid='.$tableName.'.id and itemlink.fromtype = '.$this->dbService->quote($itemType), 'itemlink.toid')
	            ->where('itemlink.fromid = ?', $item->id);
        } else {
            $select->from($tableName)
	            ->joinLeft('itemlink', 'itemlink.fromtype = '.$this->dbService->quote($type).' and itemlink.fromid='.$tableName.'.id and itemlink.totype = '.$this->dbService->quote($itemType), 'itemlink.fromid')
	            ->where('itemlink.toid = ?', $item->id);
        }

        $this->dbService->applyWhereToSelect($where, $select);
        
        $select->order($sortby);

        $objects = $this->dbService->fetchObjects($type, $select);
        
        return $objects;
    }

    /**
     * Get all items that are the pointy end of a linking tree. These items
     * exist in a 'from' relationship, but NOT as a 'to' target of a link. 
     * @return ArrayObject
     */
    public function getOrphanItems($type, $where=array(), $sortby = 'id asc') 
    {
        /* @var $select Zend_Db_Select */
        $select = $this->dbService->select();
        $tableName = mb_strtolower($this->sanitizeType($type));
        // $tableName = $this->dbService->quote(mb_strtolower($type));
        // select * from $type left join itemlink on itemlink.type = $type and itemlink.to=$type.id where itemlink.to is null 
        $select->from($tableName)
            ->joinLeft('itemlink', 'itemlink.totype = '.$this->dbService->quote($type).' and itemlink.toid='.$tableName.'.id', 'itemlink.toid')
            ->where(new Zend_Db_Expr('itemlink.toid is null '));

        foreach ($where as $field => $value) {
            if ($value instanceof Zend_Db_Expr && is_int($field)) {
                $select->where($value);
            } else { 
                $select->where($field.' ?', $value);
            }
        }
        
        $select->order($sortby);

        $objects = $this->dbService->fetchObjects($type, $select);
        return $objects;
    }
    
    /**
     * Deletes an item 
     */
    public function deleteItem($item)
    {
        try {
            $this->dbService->beginTransaction();
            $this->dbService->delete('itemlink', 'toid = '.$item->id. ' OR fromid = '.$item->id);
            $this->dbService->commit();
        } catch (Exception $e) {
            $this->log->error("Failed deleting item ".get_class($item).' #'.$item->id);
            $this->dbService->rollback();
        }
    }
    
    /**
     * The default creation method
     *
     * @param unknown_type $params
     * @return unknown
     */
    private function defaultCreate($params, $createFrom)
    {
        $data = $this->dbService->getRowFrom($createFrom);
        return $this->dbService->saveObject($data, $params['createtype']);
    }
    
    public function createTaskFromIssue($params, $createFrom=null)
    {
    	$category = 'Billable';
        if ($params instanceof Issue) {
            $createFrom = $params;
            if ($params->issuetype == 'Bug') {
            	$category = 'Support';
            }
        }
        
        $data = array();
        $data['title'] = $createFrom->title;
        $data['description'] = $createFrom->description;
        $data['userid'] = array(za()->getUser()->getUsername());
        $data['projectid'] = $createFrom->projectid;
        
        $data['category'] = $category;
        
        return $this->dbService->saveObject($data, 'Task');
    }
    
    /**
     * Create a task from a feature
     *
     * @param array $params
     * @return Task
     */
    public function createTaskFromFeature($params, $createFrom=null)
    {
        // If called directly, we don't need to worry about 
        // using the createFrom object.
        if ($params instanceof Feature) {
            $createFrom = $params;
        }

        /* @var $createFrom Feature */
        $data = array();
        $data['title'] = $createFrom->title;
        $data['description'] = $createFrom->description;
        $data['estimated'] = $createFrom->estimated * za()->getConfig('day_length', 8);
        $data['userid'] = array(za()->getUser()->getUsername());
        $data['projectid'] = $createFrom->projectid;
        
        return $this->dbService->saveObject($data, 'Task');
    }
    
    /**
     * Cleans a type up for use
     */
    private function sanitizeType($type)
    {
        return preg_replace('/[^a-z0-9_]/i', '', $type);
    }
}

class RecursiveLinkException extends Exception
{
    
}
?>