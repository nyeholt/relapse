<?php

include_once('Alfresco/Service/Session.php');
include_once('Alfresco/Service/SpacesStore.php');
include_once('Alfresco/Service/ContentData.php');

class AlfrescoController extends BaseController 
{
    
    function indexAction()
    {
        $session = Session::create("admin", "admin", "http://localhost:8080/alfresco/api");
        $spacesStore = new SpacesStore($session);
        
        $selected = $this->_getParam('id');
        
        $root = null;
        if (!$selected) {
            $root = $spacesStore->getCompanyHome();
        } else {
            
            $root = $this->getNode($session, $spacesStore, $selected);
        }
        
        /* @var $root Node */
        
        $children = $root->getChildren();
        
        if ($root->getPrimaryParent() != null) {
            echo '<a href="?id='.$root->getPrimaryParent()->getId().'">..</a><br/>';
        }
        foreach ($children as $childAssociation) {
            /* @var $childAssociation ChildAssociation */
            
            $child = $childAssociation->getChild();
            /* @var $child Node */
            
            echo '<a href="?id='.$child->getId().'">'.$childAssociation->getChild()->cm_name."</a><br/>";
        }
    }
    
    private function getNode($session, $store, $id)
    {
        $nodes = $session->query($store, '@sys\:node-uuid:"'.$id.'"');
        return isset($nodes[0]) ? $nodes[0] : null;
    }
}

?>