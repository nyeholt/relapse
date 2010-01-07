Hi <?php $this->o($this->user->username) ?>,

Your request "<?php $this->o($this->model->title);?>" has had its status changed
from <?php $this->o($this->oldStatus)?> to <?php $this->o($this->model->status)?>.

<?php 
$module = null;
if ($this->user->getRole() == User::ROLE_EXTERNAL) {
    $module = 'external';
}
?>

The request can be viewed at <?php echo user_url($this->user, 'issue', 'edit', array('id'=>$this->model->id), true, $module) ?>


This is an automatically generated email from <?php echo user_url($this->user, 'index', 'index', null, true, $module) ?>