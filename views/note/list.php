<ul>
<?php foreach ($this->notes as $note): ?>
<li>
<?php 
$this->viewNotes($note->attachedtoid, $note->attachedtotype, 'small-icon'); 

$this->o($note->title); ?>

</li>
<?php endforeach; ?>
</ul>