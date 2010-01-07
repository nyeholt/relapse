

<script type="text/javascript">
    $().ready(function() {
        $("select#clientid").change(function(){
        	$('#projectSelector-projectid').load('<?php echo build_url('project', 'projectSelector')?>', {clientid: $(this).val(), selectorType: 'project', fieldName: 'projectid'});
		});
        $("#expense-container").tabs({ fxFade: true, fxSpeed: 'fast' });
    });
</script>


<?php if ($this->model->id): ?>
<div id="parent-links">
    <?php if (isset($this->project)): ?>
        <a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->id));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
    <?php endif;?>
    <a title="Parent Client" href="<?php echo build_url('client', 'view', array('id'=>$this->client->id));?>"><img src="<?php echo resource('images/client.png')?>"/></a>
</div>
<?php endif; ?>

<h2>
<?php $this->o($this->model->id ? 'Edit Expense' : 'New Expense');?>
</h2>

<?php $disabled = $this->model->status == Expense::APPROVED; ?>

<div id="expense-container">
    <ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
        <?php if ($this->model->id): ?>
        <li><a href="#files"><span>Files</span></a></li>
        <?php endif; ?>
    </ul>
    <div id="details">
        <form class="expense-form" method="post" action="<?php echo build_url('expense', 'save');?>">
        <?php if (isset($this->project)): ?>
        <input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />
        <?php endif; ?>
        
        <?php if (isset($this->client)): ?>
        <input type="hidden" value="<?php echo $this->client->id?>" name="clientid" />
        <?php endif;?>
        
        <?php if ($this->model->id): ?>
        <input type="hidden" value="<?php echo $this->model->id?>" name="id" />
        <?php endif; ?>
        
        <div class="inner-column">
        	<?php if ($this->model->id && !empty($this->model->paiddate)): ?>
        	<p>
		    Paid on:<br />
		    <?php $this->o($this->u()->formatDate($this->model->paiddate)); ?>
		    </p>
        	<?php endif; ?>
        	
        	<?php $this->textInput('Description', 'description') ?>
			<?php $this->priceInput('Amount', 'amount', $disabled ? 'disabled="disabled"' : '') ?>

            <p>
		    <label for="expensedate">Expense Date:</label>
		    <input <?php echo $disabled ? 'disabled="disabled"' : ''?> readonly="readonly" type="text" class="input" name="expensedate" id="expensedate" value="<?php echo $this->model->expensedate ? date('Y-m-d', strtotime($this->model->expensedate)) : date('Y-m-d', time())?>" />
		    <?php $this->calendar('expensedate', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
		    </p>

            <?php
            $this->selectList('Type', 'expensetype', $this->expenseTypes);
            ?>
        	

            <?php
            $this->selectList('Category', 'expensecategory', $this->expenseCategories);
            ?>
			<p>
        	<?php $this->textInput('GST', 'gst', false, '', 10, "%") ?>
        	</p>
        </div>
        <div class="inner-column">
        	<?php $this->selectList('Expensed By', 'username', $this->users, $this->u()->getUsername(), 'username', 'username'); ?>
        	<?php $this->valueList('Location', 'location', 'expense-form', $this->locations) ?>

            <p>
            <label for="clientid">Client:</label>
            <select <?php echo $disabled ? 'disabled="disabled"' : ''?> name="clientid" id="clientid">
            	<option></option>
                <?php 
                $sel = $this->client->id;
                foreach ($this->clients as $client): ?>
                    <option value="<?php $this->o($client->id)?>" <?php echo $sel == $client->id ? 'selected="selected"' : '';?>><?php $this->o($client->title);?></option>
                <?php endforeach; ?>
            </select>
            </p>
            <p>
	            <label for="projectid">Project:</label>
	            <?php $this->projectSelector('projectid', $this->projects, 'project', false, $this->defaultProjectid) ?>
            </p>
            
            <?php
            $this->selectList('ATO Category', 'atocategory', $this->categories);
            ?>
        </div>
        <p class="clear">
            <input <?php echo $disabled ? 'disabled="disabled"' : ''?> type="submit" class="abutton" value="Save" accesskey="s" />
            <?php if (isset($this->project)): ?>
	        <input type="button" class="abutton" onclick="location.href='<?php echo build_url('project', 'view', array('id'=>$this->project->id, '#expenses'))?>'" value="Close"></input>
	        <?php endif; ?>
	        
	        <?php if (isset($this->client)): ?>
	        <input type="button" class="abutton" onclick="location.href='<?php echo build_url('client', 'view', array('id'=>$this->client->id, '#expenses'))?>'" value="Close"></input>
	        <?php endif;?>

        </p>
        </form>
    </div>
    
    <?php if ($this->model->id): ?>
    <div id="files">
	    <h2>
	    Files
	    </h2>
	    <div>
	    	<ul id="file-listing">
				<?php foreach ($this->files as $file):?>
				    <li>
				    <?php if (is_string($file)): ?>
				    <?php else: ?>
				        <a class="action-icon" title="Edit file" href="<?php echo build_url('file', 'edit', array('id'=>$file->id, 'parent'=>base64_encode($file->path)))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
				        <a class="action-icon" title="Delete file" href="<?php echo build_url('file', 'delete', array('id'=>$file->id))?>"><img src="<?php echo resource('images/delete.png')?>" /></a>
				        <a href="<?php echo build_url('file', 'view', array('id' => $file->id)).htmlentities($file->filename)?>"><?php $this->o($file->getTitle())?></a>
				        <?php if (!empty($file->description)): ?>
				        <p>
				            <?php $this->o($file->description) ?>
				        </p>
				        <?php endif; ?>
				    <?php endif; ?>    
				    </li>
				<?php endforeach; ?>
			</ul>
	    </div>
	   	<hr/>
	    <div>
	    <h3>Add New File</h3>
	    	<form method="post" action="<?php echo build_url('file', 'upload');?>" enctype="multipart/form-data">
			<input type="hidden" name="returnurl" value="<?php echo base64_encode('expense/edit/id/'.$this->model->id.'/clientid/'.$this->model->clientid.'#files')?>" />
			<input type="hidden" value="<?php echo base64_encode($this->filePath)?>" name="parent" />
			
			<div class="inner-column">
			    <p>
			    <label for="file">File</label>
			    <input class="input" type="file" name="file" id="file" />
			    </p>
			    <p>
			    <label for="filename">Filename:</label>
			    <input class="input" type="text" name="filename" id="filename"/>
			    </p>
			    <p>
			    <label for="title">Title:</label>
			    <input class="input" type="text" name="title" id="title" />
			    </p>
			    
			</div>
			<div class="inner-column">
			    <p>
			    <label for="description">Description:</label>
			    <textarea class="input" name="description" 
			        id="description"></textarea>
			    </p>
			</div>
			<p class="clear">
			    <input type="submit" class="button" value="Upload" accesskey="u" />
			</p>
			</form>
	    </div>
	</div>
    <?php endif; ?>
<!--/expense-container-->
</div>