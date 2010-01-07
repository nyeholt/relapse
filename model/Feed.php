<?php

class Feed extends Bindable 
{
    public $id;
    public $title;
    public $created;
    public $updated;
    
    public $url;
    
    /**
     * The content of the feed, serialized
     *
     * @var object
     */
    public $content;
    
    private $items;
    
    public $searchableFields = array();
    
    /**
     * Get the posts in this feed. 
     *
     */
    public function getPosts()
    {
        if ($this->items == null) {
            $this->items = array();
            $doc = null;
            try {
                $doc = new SimpleXmlElement($this->content);
	            $items = $doc->item;
	            foreach ($items as $item) {
	                $dc = $item->children('http://purl.org/dc/elements/1.1/'); 
	
	                $this->items[] = array(
	                    'title' => $item->title,
	                    'link' => $item->link,
	                    'description' => $dc->subject,
	                    'date' => $dc->date,
	                    'creator' => $dc->creator,
	                );
	            }
            } catch (Exception $e) {
                za()->log("Failed parsing feed", Zend_Log::ERR);
            }
        }

        return $this->items;
    }
}
?>