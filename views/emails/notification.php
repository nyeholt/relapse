<?php echo $this->model->note; ?>


<?php echo $this->model->userid ?>


<?php 
if ($this->toUrl != null) {
    echo user_url($this->user, ifset($this->toUrl, 'controller'), ifset($this->toUrl, 'action'), ifset($this->toUrl, 'params', ''), true);
}

?>