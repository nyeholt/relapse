<?php
class TagService
{
    const MIN_SUGGESTED_TAG = 5;
    const MAX_SUGGESTED_TAG = 10;
    /**
     *
     * @var DbService
     */
    public $dbService;

    /**
     * Save tags for a given item
     *
     * @param object $item
     * @param string $tags
     */
    public function saveTags($item, $tags)
    {
        $type = mb_strtolower(get_class($item));
        $id = (int) $item->id;

        if (!$id || !mb_strlen($type)) {
            throw new Exception("Cannot delete tags for $item");
        }
        // delete all tags for an object
        $this->deleteTags($item, za()->getUser());

        // okay, now for everything in tags, we need to create a new one
        $tags = mb_split(",", $tags);
        foreach ($tags as $tag) {
            $tag = $this->normaliseTag($tag);
            if (mb_strlen($tag) <= 2) {
                continue;
            }
            $params = array();

            $params['uid'] = za()->getUser()->getId();
            $params['itemtype'] = $type;
            $params['itemid'] = $id;
            $params['tag'] = $tag;

            $this->dbService->saveObject($params, 'Tag');
        }
    }

    /**
     * Make sure a tag is in the correct format
     */
    protected function normaliseTag($tag)
    {
        return trim(mb_strtolower($tag));
    }

    /**
     * Get the tags for a given item
     */
    public function getItemTags($item)
    {
        $type = mb_strtolower(get_class($item));
        $id = (int) $item->id;

        $tags = $this->dbService->getObjects('Tag', array('itemtype='=>$type, 'itemid='=>$id));
        return $tags;
    }

    /**
     * Get suggested tags for a given stub
     * @param String $stub
     * @return array
     */
    public function getTags($where=array(), $order = "frequency desc", $number = null, $minFrequency=null)
    {
        /* @var $select Zend_Db_Select */
        $select = $this->dbService->select();
        $select->from('tag', array('tag', new Zend_Db_Expr('count(tag) as frequency')));

        foreach ($where as $field => $value) {
            $select->where($field.' ?', $value);
        }

        $select->group('tag');
        $select->order($order);

        if ($number) {
            $select->limitPage(1, $number);
        }

        if ($minFrequency) {
            $select->having('frequency > '.$minFrequency);
        }

        $result = $this->dbService->query($select, null);

        $tags = new ArrayObject($result->fetchAll(Zend_Db::FETCH_ASSOC));

        return $tags;
    }

    /**
     * Get the IDs of all items tagged with a certain tag
     * @return array the item ids
     */
    public function getTaggedItems($tag, $type=null, $order="id desc", $page=null, $number=null)
    {
        $select = $this->dbService->select();
        $select->from('tag', array('itemid'));
        if ($type) {
            $select->where('itemtype= ?', mb_strtolower($type));
        }
        $select->where('tag = ?', $this->normaliseTag($tag));
        $select->order($order);

        if (!is_null($page)) {
            $select->limitPage($page, $number);
        }

        $result = $this->dbService->query($select, null);

        $ids = array();
        while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
            $ids[] = $row['itemid'];
        }
        return $ids;
    }

    /**
     * Get a list of related items for the passed in item.
     * 
     * works by comparing the list of things this item has against what all other items have, 
     * and returns the results ordered by the item with the most matches
     */
    public function getRelatedItems($item, $type = null)
    {
        // First get the tags for this item
        $tags = $this->getItemTags($item);
        $in = '';
        $sep = '';
        foreach ($tags as $tag) {
            $in .= $sep . $this->dbService->quote($tag->tag);
            $sep = ',';
        }
        if (!mb_strlen($in)) {
            return new ArrayObject();
        }

        $in = '('.$in.')';
        
        // Alright, now lets query for those items with these tags, ordering by the most tagged	
        $select = $this->dbService->select();
        $select->from('tag', array('tag', 'itemid', 'itemtype', new Zend_Db_Expr('count(itemid) as score')));
        $select->where(new Zend_Db_Expr('tag in '.$in)); 
        if ($type) {
            $select->where('itemtype=?', $type);
        }
        
        // Make sure to ignore the current object
        $select->where('itemid<>?', $item->id);
        $select->where('itemtype<>?', mb_strtolower(get_class($item)));
        
        $select->group('itemid'); 
        $select->order('score desc');

        $result = $this->dbService->query($select, null);

        $items = new ArrayObject();
        $tags = $result->fetchAll(Zend_Db::FETCH_ASSOC);
        foreach ($tags as $tag) {
            // get the item
            $items[] = $this->dbService->getById($tag['itemid'], $tag['itemtype']);
        }
        
        return $items;
    }

    /**
     * For the passed in content, figure out some potential tags. 
     * 
     * @return array a list of tags. 
     */
    public function suggestTagsFor($content, $type=null)
    {
        // replace all HTML tags first
        $content = strip_tags($content);
        
        // split it into words, 
        $words = preg_split('/[^\w-_]/', $content);
        
        $return = array();
        // foreach word, see if there's a tagged item
        foreach ($words as $word) {
            // only suggest longish tags
            if (mb_strlen($word) < self::MIN_SUGGESTED_TAG || mb_strlen($word) > self::MAX_SUGGESTED_TAG) {
                continue;
            }

            // get all items tagged with this word
            $items = $this->getTaggedItems($word, $type);
            $this->log->debug("Found ".count($items)." tags for $word");
            if (count($items)) {
                // okay, probably a reasonable tag to use
                $return[] = $word;
            }
        }

        return $return;
    }
    
    /**
     * Delete the tags for the given object
     *
     * @param object $item
     */
    public function deleteTags($item)
    {
        $type = mb_strtolower(get_class($item));
        $id = (int) $item->id;
        // okay, delete ahoy
        $this->dbService->delete('tag', 'itemtype='.$this->dbService->quote($type).' and itemid='.$id);
    }
}
?>