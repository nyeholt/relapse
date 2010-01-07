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

class SearchService implements Configurable 
{
    /**
     * The path to the index
     *
     * @var string
     */
    private $indexPath;
    
    /**
     * The lucene index object
     *
     * @var Zend_Search_Lucene 
     */
    private $index;
    
    public function configure($config)
    {
        if (isset($config['index'])) {
            try {
	            Zend_Search_Lucene_Analysis_Analyzer::setDefault(new AlphaNumAnalyzer());
	            
	            $this->indexPath = BASE_DIR.$config['index'];
	            if (!is_dir($this->indexPath)) {
	                $this->index = Zend_Search_Lucene::create($this->indexPath);
	            } else {
	                $this->index = Zend_Search_Lucene::open($this->indexPath);
	            }
            } catch (Zend_Search_Lucene_Exception $zsle) {
                // probably a readonly index? 
                // ignore so we can continue.
                za()->log("Failed opening index: ".$zsle->getMessage(), Zend_Log::ERR);
                za()->log($zsle->getTraceAsString(), Zend_Log::ERR);
            }
        }
    }
    
    /**
     * Index an object. 
     *
     * @param mixed $item
     */
    public function index($item)
    {
        /**
         * Never index users.
         */
        if ($item instanceof NovemberUser) return;
        if (!$this->index) return;
        // Get the document ID. If it doesn't exist, 
        // quit, otherwise we'll never be able to figure out
        // what to do with it. 
        if (!isset($item->id)) {
            return;
        }
        
        $type = strtolower(get_class($item));
        // $id = $this->convertToChars($item->id).$type;
        $id = $item->id.$type;
        
        $title = isset($item->title) ? $item->title : '';
        $name = isset($item->name) ? $item->name : $title;
        
        $description = isset($item->description) ? $item->description : '';
        
        // First see if the document exists. If it does, we need to
        // remove it first.
        $hits = $this->index->find("identifier:'".$id."'");
        foreach ($hits as $hit) {
            /* @var $hit Zend_Search_Lucene_Search_QueryHit */
            $this->index->delete($hit->id);
        }
        
        $doc = new Zend_Search_Lucene_Document();
        /* @var $doc Zend_Search_Lucene_Document */
        $doc->addField(Zend_Search_Lucene_Field::Keyword('identifier', $id));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('type', $type));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('id', $item->id));
        
        /**
         * To be indexed, you MUST set the searchableFields array
         */
        if (isset($item->searchableFields)) {
            if (count($item->searchableFields) == 0) {
                return;
            }
            foreach ($item->searchableFields as $field) {
                // get the value and add it, as below. 
                $this->addPropertyValueToDocument($doc, $field, $item->$field);
            }
        } else {
	        // Use reflection class to prevent reflectionObject finding
	        // dynamically assigned properties.
	        /*$obj = new ReflectionClass(get_class($item));
	        $props = $obj->getProperties();
	        foreach ($props as $property) {
	            if ($property->isPublic()) {
	                $prop = $property->getName();
	                if ($prop == 'constraints' || $prop == 'requiredFields' || $prop == 'searchableFields') continue;
	                $value = $item->$prop;
	                if (is_null($value)) continue;
	
	                $this->addPropertyValueToDocument($doc, $property->getName(), $value);
	            }
	        }*/
            return;
        }
        
        $this->log->debug("Indexing document ".$item->id);
        $this->index->addDocument($doc);
    }

    /**
     * Add a property value to a document
     */
    protected function addPropertyValueToDocument($doc, $propName, $value)
    {
        // we add two fields; one for viewing the data later,
        $doc->addField(Zend_Search_Lucene_field::UnIndexed($propName, $value));
        // And one that actually gets indexed.
        $doc->addField(Zend_Search_Lucene_field::UnStored('_'.$propName, strtolower($value)));
    }
    
    /**
     * Delete an item from the index
     *
     * @param unknown_type $item
     */
    public function delete($item)
    {
        
        if (!$this->index) return;
        
        // Get the document ID. If it doesn't exist, 
        // quit, otherwise we'll never be able to figure out
        // what to do with it. 
        if (!isset($item->id)) {
            return;
        }
        
        $type = strtolower(get_class($item));
        // $id = $this->convertToChars($item->id).$type;
        $id = $item->id.$type;
        
        // First see if the document exists. If it does, we need to
        // remove it first.
        $hits = $this->index->find("identifier:'".$id."'");
        foreach ($hits as $hit) {
            /* @var $hit Zend_Search_Lucene_Search_QueryHit */
            $this->index->delete($hit->id);
        }
    }
    
    /**
     * Delete a hit. This can be useful for dynamically purging stuff.
     */
    public function deleteHit($hit)
    {
        $this->index->delete($hit->id);
    }
    
    /**
     * Search for a document
     *
     * @param string $query
     */
    public function search($query)
    {
        if (!$this->index) return;
        $hits = $this->index->find(strtolower($query));
        return $hits;
    }
    
    /**
     * Lucene won't let us have anything other than letters for a
     * unique identifier, so what we'll do is convert it to an 
     * ascii character
     *
     * @param int $int
     */
    private function convertToChars($int)
    {
        // We'll cheat and convert it to a string, then
        // do each character on its own
        $int = "$int";
        $new = "";
        for ($i=0, $l=strlen($int); $i < $l; $i++) {
            $new .= chr(97+$int{$i});
        }
        return $new;
    }
}


include_once 'Zend/Search/Lucene/Analysis/Analyzer.php';
/** 
 * Here is a custom text analyser, 
 * which treats words with digits as one term 
 * @see http://framework.zend.com/manual/en/zend.search.extending.html#zend.search.extending.analysis
 * 
 */
class AlphaNumAnalyzer extends Zend_Search_Lucene_Analysis_Analyzer_Common
{
    private $_position;

    /**
     * Reset token stream
     */
    public function reset()
    {
        $this->_position = 0;
    }

    /**
     * Tokenization stream API
     * Get next token
     * Returns null at the end of stream
     *
     * @return Zend_Search_Lucene_Analysis_Token|null
     */
    public function nextToken()
    {
        if ($this->_input === null) {
            return null;
        }

        while ($this->_position < strlen($this->_input)) {
            // skip white space
            while ($this->_position < strlen($this->_input) &&
                   !ctype_alnum( $this->_input[$this->_position] )) {
                $this->_position++;
            }

            $termStartPosition = $this->_position;

            // read token
            while ($this->_position < strlen($this->_input) &&
                   ctype_alnum( $this->_input[$this->_position] )) {
                $this->_position++;
            }

            // Empty token, end of stream.
            if ($this->_position == $termStartPosition) {
                return null;
            }

            $token = new Zend_Search_Lucene_Analysis_Token(
                                      substr($this->_input,
                                             $termStartPosition,
                                             $this->_position - $termStartPosition),
                                      $termStartPosition,
                                      $this->_position);
            $token = $this->normalize($token);
            if ($token !== null) {
                return $token;
            }
            // Continue if token is skipped
        }

        return null;
    }
}
?>