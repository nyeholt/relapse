<?php

/**
 * Performs the linking of two objects. Also allows
 * for the creation of a new object based on another.
 *
 */
class ItemLinkController extends BaseController 
{
    /**
     * The ItemLinkerService
     *
     * @var ItemLinkerService
     */
    public $itemLinkerService;
    
    /**
     * Creates a new model object based on an existing one stored
     * somewhere in the system.
     */
    public function createbasedonAction()
    {
        $item = $this->itemLinkerService->createNewItem($this->_getAllParams());
    }
}
?>