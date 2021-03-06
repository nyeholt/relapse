<?php 

/**
 * Represents a watch that a user has on an item in the system.
 *
 */
class ItemWatch extends MappedObject
{
    public $itemid;
    public $itemtype;
    /**
     * What should be shown as the target ?
     *
     * @var string
     */
    public $userid;
    
    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
    }
}

?>