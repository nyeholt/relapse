<?php 

include_once dirname(__FILE__).'/O.php';
include_once dirname(__FILE__).'/ViewNotes.php';

class Helper_ShowSearchResult
{
    public function ShowSearchResult($result)
    {

        try {
            $result->__get('identifier');
        } catch (Zend_Search_Lucene_Exception $zse) {
            za()->log("Failed to retrieve result ".$zse->getMessage());
            return;
        }

        try {
            $result->__get('id');
        } catch (Zend_Search_Lucene_Exception $zse) {
            za()->log("Failed to retrieve result for ".$result->identifier);
            return;
        }

        $shown = false;
        
        $printer = 'show'.$result->type;
        if (method_exists($this, $printer)) {
            $this->$printer($result);
            $shown = true;
        } 

        return $shown;
    }
    
    function showDefault($result) {
        echo '<li class="search-result">';
    
        echo '<h3><a target="CenterPane" class="targeted" href="'.build_url($result->type, 'view', array('id'=>$result->__get('id'))).'">'.$result->title.'</a> ('.ucfirst($result->type).')</h3>';
        if (isset($result->description)) {
            @print($result->description);
        }
        echo '</li>';
    }
    
    function showClient($result)
    {
        return $this->showDefault($result);
    }
    

    function showIssue($result)
    {
        echo '<li class="search-result">';
    
        echo '<h3><a target="RightPane" class="targeted" href="'.build_url($result->type, 'edit', array('id'=>$result->__get('id'))).'">'.$result->title.'</a> ('.ucfirst($result->type).')</h3>';
        if (isset($result->description)) {
            @print($result->description);
        }
        echo '</li>';
    }
    
    function showTask($result) {
        echo '<li class="search-result">';
        echo '<h3><a target="RightPane" class="targeted" href="'.build_url('task', 'edit', array('id'=>$result->__get('id'))).'">'.$result->title.'</a> ('.ucfirst($result->type).')</h3>';
        print($result->description);
        echo '</li>';
    }
    
    function showProject($result) {
        echo '<li class="search-result">';
        echo '<h3><a target="CenterPane" class="targeted" href="'.build_url('project', 'view', array('id'=>$result->__get('id'))).'">'.$result->title.'</a> ('.ucfirst($result->type).')</h3>';
    }
    
    function showNote($result) {
        echo '<li class="search-result">';
        echo '<h3><a href="#" onclick="viewNotes('.$result->attachedtoid.', \''.$result->attachedtotype.'\'); return false;">'.$result->title.'</a> ('.ucfirst($result->type).')</h3>';
        print ($result->note);
        echo '</li>';
    }
    
    function showContact($result) {
        
        $firstname = '[unknown]';
        $lastname = '';
        $title = '';
        try {
            $firstname = $result->__get('firstname');
        } catch (Zend_Search_Lucene_Exception $zse) {
            
        }
        
        try {
            $lastname = $result->__get('lastname');
        } catch (Zend_Search_Lucene_Exception $zse) {
            
        }
        
        echo '<li class="search-result">';
        echo '<h3><a target="RightPane" class="targeted" href="'.build_url('contact', 'edit', array('id'=>$result->__get('id'))).'">'.$firstname.' '.$lastname.'</a></h3>';
        echo '<p>Email: <a href="mailto:'.$result->email.'">'.$result->email.'</a></p>'.(isset($result->mobile) ? '<p>Mobile: '.$result->mobile.'</p>' : '');
        echo '</li>';
    }
    
    function showFaq($result) {
        $this->showDefault($result);
    }

    function showTag($result) {
        return;
    }
}
?>