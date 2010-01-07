
<ul>
<?php 
foreach ($this->users as $user) {
    ?>
     <li>
        <a href="<?php echo build_url('user', 'view', array('id' => $user->getId()));?>">
            <?php $this->o($user->username);?>
        </a>
        
     </li>  
    <?php
}
?>
</ul>