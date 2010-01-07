<h2>Details for <?php $this->o($this->user->username)?></h2>

<div class="inner-column">
    <div class="micro-column">
        <p>
            <strong>Username</strong><br/>
            <?php $this->o($this->user->username) ?>
        </p>
        <p>
            <strong>Email</strong><br/>
            <a href="mailto:<?php echo $this->user->email?>"><?php echo $this->user->email?></a>
        </p>
    </div>
</div>