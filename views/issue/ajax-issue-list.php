<ul>
<?php foreach ($this->issues as $issue): ?>
    <li>
        <a class="action-icon" title="Edit Request" href="<?php echo build_url('issue', 'edit', array('id' => $issue->id, 'clientid'=>$issue->clientid, 'projectid'=>$issue->projectid))?>"><img class="small-icon" src="<?php echo resource('images/pencil.png')?>" /></a>
        <?php $this->viewNotes($issue->id, 'issue', 'small-icon');?>
        
        <a title="Show Details" href="#" onclick="$('#<?php echo $this->type?>-issue-details-<?php echo $issue->id?>').toggle(); return false;"><?php $this->o($issue->title)?></a> 
        
        <div id="<?php echo $this->type?>-issue-details-<?php echo $issue->id?>" class="hidden-info">
        <div class="micro-column gainlayout">
            <p>
                <strong>Title</strong><br/>
                <?php $this->o($issue->title) ?>
            </p>
            <p>
                <strong>Description</strong><br/>
                <?php $this->o($issue->description, true) ?>
            </p>
            <p>
                <strong>Assigned To</strong><br/>
                <?php $this->o($issue->userid) ?>
            </p>
        </div>
        <div class="micro-column gainlayout">
            <p>
                <strong>Severity</strong><br/>
                <?php $this->o($issue->severity) ?>
            </p>
            <p>
                <strong>Type</strong><br/>
                <?php $this->o($issue->issuetype) ?> 
            </p>
            <p>
                <strong>Created</strong><br/>
                <?php $this->o($issue->created) ?> 
            </p>
            <p>
                <strong>Last Updated</strong><br/>
                <?php $this->o($issue->updated) ?> 
            </p>
        </div>
        <div class="clear" />
        </div>
    </li>
<?php endforeach; ?>
</ul>