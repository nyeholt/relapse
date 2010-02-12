<tr>

   <?php //$this->addNote($this->task->title, $this->task->id, 'task', 'small-icon');?>
   <?php //$this->viewNotes($this->task->id, 'task', 'small-icon');?>
   <td>
	<a title="Show Details" href="<?php echo build_url('task', 'edit', array('id' => $this->task->id, 'projectid'=>$this->project->id))?>"><?php $this->o($this->task->title)?></a>    
	
	<?php if ($this->showActions): ?>
	<!-- Task timer stuff -->
	   <div style="display: none;" id="task-<?php echo $this->task->id?>-timesheet">
	    <form id="task-<?php echo $this->task->id?>-timesheet-form2" action="<?php echo build_url('timesheet', 'addtime', array('taskid'=>$this->task->id))?>" method="post">
	     From <input type="text" id="beginning-<?php echo $this->task->id?>" name="start" size="15" />, 
	     add <input type="text" id="total-<?php echo $this->task->id?>" name="total" size="3" /> hours.
	     <input type="submit" value="Add" />
	     <?php $this->calendar('beginning-'.$this->task->id) ?>
	     
	     <script type="text/javascript">
	         $(document).ready(
	             function() {
	                 var addTaskNoteForm = $('#task-<?php echo $this->task->id?>-timesheet-form2');
	                 addTaskNoteForm.ajaxForm(function() { 
	                     $('#task-<?php echo $this->task->id?>-timesheet-detail').load('<?php echo build_url('timesheet','detailedTimesheet', array('taskid'=>$this->task->id))?>');
	                 });
	             }
	         );
	     </script>
	    </form>
	
	    <div id="task-<?php echo $this->task->id?>-timesheet-detail">
	    </div>
	   </div>
   <?php endif; ?>
   
   </td>
	<td>
	<?php $this->o(date('Y-m-d', strtotime($this->task->due)).' ('.sprintf('%.2f', $this->task->getPercentage()).'%)'); ?>
	</td>
	
	<td>
	<?php 
        if (is_array($this->task->userid)) {
         foreach ($this->task->userid as $userid) {
         	$this->o($userid);echo '<br/>';
         }
        }
        ?>
	</td>

	<td>
	<a title="Delete Task" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('task', 'delete', array('id' => $this->task->id, 'projectid'=>$this->project->id))?>'; return false;" href="#"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a>
	<?php if ($this->showActions): ?>
	<a title="Add Timsheet Record" href="#" 
	onclick="$('#task-<?php echo $this->task->id?>-timesheet').toggle(); $('#task-<?php echo $this->task->id?>-timesheet-detail').load('<?php echo build_url('timesheet','detailedTimesheet', array('taskid'=>$this->task->id))?>'); return false;"><img class="small-icon" src="<?php echo resource('images/clock_play.png')?>" /></a>
	<?php endif; ?>
	</td>   
   
   

</tr>