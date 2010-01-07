<div id="parent-links">
    <a title="Goto User" href="<?php echo build_url('user', 'edit', array('id'=>$this->user->id, '#reviews'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
</div>


<script type="text/javascript">
    $().ready(function() {
        
        $("#review-container").tabs({ fxFade: true, fxSpeed: 'fast' });
    });
</script>

<h2>
<?php $this->o($this->model->id ? 'Edit "'.$this->model->title .'"': 'New Performance Review');?>
</h2>

<div id="review-container" class="wide-form">
	<ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
        <?php if ($this->model->id): ?>
        <li><a href="#versions"><span>History</span></a></li>
        
        <?php endif; ?>
    </ul>
	
	<div id="details">
	<form method="post" action="<?php echo build_url('performancereview', 'save');?>">
		<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
		<input type="hidden" name="username" value="<?php $this->o($this->user->username)?>" />
		<?php $this->textInput('Title', 'title'); ?>
		<?php $this->calendarInput('From', 'from'); ?>
		<?php $this->calendarInput('To', 'to'); ?>
		<?php $this->textInput('Position', 'position'); ?>
		<?php $this->selectList('Reports To', 'reportsto', $this->users, '', 'username', 'username', false, true); ?>

<table style="display: none;">
<tbody id="goal-template">
<tr class="goal-row">
	<td>
	<input class="goal-input" type="text" value="{type}" name="{goalname}[{goalnumber}][type]" ></input>
	</td>
	<td>
	<textarea class="goal-input" name="{goalname}[{goalnumber}][actions]">{actions}</textarea>
	</td>
	<td>
	<input class="goal-input" id="goal-due-{goalname}-{goalnumber}" readonly="readonly" type="text" name="{goalname}[{goalnumber}][due]" value="{due}" />
	</td>
	<td>
	<textarea class="goal-input" name="{goalname}[{goalnumber}][results]">{results}</textarea>
	</td>
	<td>
	<img style="float: right;" src="<?php echo resource('images/delete.png')?>" onclick="$(this).parents('.goal-row').remove()" />
	</td>
</tr>
</tbody>
</table>

<script type="text/javascript">
function addGoal(id, name, goal)
{
	var type = goal != null ? goal.type : '&nbsp;';
	var actions = goal != null ? goal.actions : '';
	var due = goal != null ? goal.due : '<?php echo date('Y-m-d', time() + 86400 * 30)?>';
	var results = goal != null ? goal.results : '';
	
	// get the goal number by figuring out how many goals are in there already
	var container = $('#'+id);
	var existingGoals = container.children('.goal-row');
	var goalNumber = existingGoals.length;
	
	var template = $('#goal-template').html();

	template = template.replace(/{type}/g, type);
	template = template.replace(/{actions}/g, actions);
	template = template.replace(/{due}/g, due);
	template = template.replace(/{results}/g, results);
	
	template = template.replace(/{goalname}/g, name);
	template = template.replace(/{goalnumber}/g, goalNumber);
	container.append(template);
	
	var goalCal = "goal-due-"+name+"-"+goalNumber; 
	var options = {inputField : goalCal, ifFormat: "%Y-%m-%d", showsTime:false };
	Calendar.setup(options);
}
</script>
	
	<h4>Short Term Goals: </h4>
	<table class="goal-table">
	<thead>
		<tr>
			<th>Type</th>
			<th>Actions</th>
			<th>Due</th>
			<th>Outcome</th>
			<th></th>
		</tr>
	</thead>
	<tbody id="short-term-goals">
	
	</tbody>
	</table>
	
	<p>
		<input type="button" value="Add Goal" class="abutton"onclick="addGoal('short-term-goals', 'shortgoals', null)" />
	</p>
	
	<?php
    $shortgoals = $this->model->shortgoals;
    if (!is_array($shortgoals)) {
        $shortgoals = array();
    }
    
    // for each short goal, we have a bunch of inputs and information
       ?>
       <script type="text/javascript">
       <?php foreach ($shortgoals as $goal): ?>
       addGoal('short-term-goals', 'shortgoals', <?php echo Zend_Json_Encoder::encode($goal)?>);
       <?php endforeach; ?>
       </script>

	<h4>Medium Term Goals: </h4>
	<table class="goal-table">
	<thead>
		<tr>
			<th>Type</th>
			<th>Actions</th>
			<th>Due</th>
			<th>Outcome</th>
			<th></th>
		</tr>
	</thead>
	<tbody id="medium-term-goals">
	
	</tbody>
	</table>
	
	<p>
		<input type="button" value="Add Goal" class="abutton"onclick="addGoal('medium-term-goals', 'mediumgoals', null)" />
	</p>
	<?php
    $goals = $this->model->mediumgoals;
    if (!is_array($goals)) {
        $goals = array();
    }
    
    // for each short goal, we have a bunch of inputs and information
       ?>
       <script type="text/javascript">
       <?php foreach ($goals as $goal): ?>
       addGoal('medium-term-goals', 'mediumgoals', <?php echo Zend_Json_Encoder::encode($goal)?>);
       <?php endforeach; ?>
       </script>
       

	<h4>Long Term Goals: </h4>
	<table class="goal-table">
	<thead>
		<tr>
			<th>Type</th>
			<th>Actions</th>
			<th>Due</th>
			<th>Outcome</th>
			<th></th>
		</tr>
	</thead>
	<tbody id="long-term-goals">
	
	</tbody>
	</table>
	
	<p>
		<input type="button" value="Add Goal" class="abutton"onclick="addGoal('long-term-goals', 'longgoals', null)" />
	</p>
	
	<?php
    $goals = $this->model->longgoals;
    if (!is_array($goals)) {
        $goals = array();
    }
    
    // for each short goal, we have a bunch of inputs and information
       ?>
       <script type="text/javascript">
       <?php foreach ($goals as $goal): ?>
       addGoal('long-term-goals', 'longgoals', <?php echo Zend_Json_Encoder::encode($goal)?>);
       <?php endforeach; ?>
       </script>
	
	<h4>Developmental Goals: </h4>
	<table class="goal-table">
	<thead>
		<tr>
			<th>Type</th>
			<th>Actions</th>
			<th>Due</th>
			<th>Outcome</th>
			<th></th>
		</tr>
	</thead>
	<tbody id="dev-goals">
	
	</tbody>
	</table>
	
	<p>
		<input type="button" value="Add Goal" class="abutton"onclick="addGoal('dev-goals', 'development', null)" />
	</p>
	
	<?php
    $goals = $this->model->development;
    if (!is_array($goals)) {
        $goals = array();
    }
    
    // for each short goal, we have a bunch of inputs and information
       ?>
       <script type="text/javascript">
       <?php foreach ($goals as $goal): ?>
       addGoal('dev-goals', 'development', <?php echo Zend_Json_Encoder::encode($goal)?>);
       <?php endforeach; ?>
       </script>
	
		<?php $this->textInput('Intermediate Reviews', 'intermediatereviews', true); ?>
		<?php $this->calendarInput('Signed by Employee', 'signedemployee'); ?>
		<div class="clear"></div>
		<?php $this->calendarInput('Signed by Manager', 'signedmanager'); ?>
		<div class="clear"></div>
		<?php $this->textInput('Employee Comments', 'employeecomments', true); ?>
		<?php $this->textInput('Manager Comments', 'managercomments', true); ?>
		
		<p>
		<input class="abutton" type="submit" value="Save" accesskey="s" />
		<a class="abutton" title="Goto User" href="<?php echo build_url('user', 'edit', array('id'=>$this->user->id, '#reviews'));?>">Done</a>
		</p>
	</form>
	</div>
	<div id="versions">
	
	<table class="item-table" cellpadding="0" cellspacing="0">
	    <thead>
	    <tr>
	        <th width="35%">Title</th>
	        <th>From</th>
	        <th>To</th>
	        <th>Created</th>
	    </tr>
	    </thead>
	    <tbody>
	    <?php foreach ($this->versions as $item):?>
	    	<tr>
		        <td><a href="<?php echo build_url('performancereview', 'view', array('id' => $item->id))?>">
                <?php $this->o($item->title)?>
    			</a></td>
		        <td style="text-align: center"><?php $this->o(date('Y-m-d', strtotime($item->from)))?></td>
		        <td style="text-align: center"><?php $this->o(date('Y-m-d', strtotime($item->to)))?></td>
				<td style="text-align: center"><?php $this->o(date('Y-m-d H:i:s', strtotime($item->created)))?></td>
		    </tr>
	    <?php endforeach; ?>
	    </tbody>
	</table>
	</div>
</div>