<?php

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_NoteList extends NovemberHelper
{
	public function NoteList($notes, $createurl, $userlist = array(), $watchers=array())
	{
		?>
		<div id="notes" class="bordered">
	        <?php $note = null;?>
	        <?php foreach ($notes as $note): ?>
			<div class="note-holder">
				<label class="note-by">By <?php $this->view->o($note->userid)?><br/><?php $this->view->o($note->created)?></label>
				<div class="note">
					<div class="note-header">
					<?php if ($this->view->u()->hasRole(User::ROLE_USER)): ?>

					<a style="float: right; " href="#" onclick="if (confirm('Do you want to delete this note?')) { $.post('<?php echo build_url('note', 'delete', array('_ajax' => 1, 'id'=>$note->id, '__validation_token' => $this->view->requestValidator(true)))?>'); $(this).parents('div.note-holder').fadeOut('slow'); } return false;"><img src="<?php echo resource('images/delete.png')?>" /></a>
					<?php endif; ?>
						<h4><?php $this->view->o($note->title)?></h4>
					</div>
					<div class="note-body" id="note-body-<?php echo $note->id?>"><?php $this->view->bbCode($note->note);?></div>

					<?php if ($note->userid == $this->view->u()->username || $this->view->u()->hasRole(User::ROLE_USER)): ?>
					<script type="text/javascript">
					 $(document).ready(function() {
						 $('#note-body-<?php echo $note->id?>').editable('<?php echo build_url('note', 'ajaxupdate', array('id'=> $note->id));?>', {
							 loadurl  : '<?php echo build_url('note', 'loadSource', array('id'=> $note->id));?>',
							 type    : 'textarea',
							 submit  : 'Done',
							 indicator : 'Saving...',
							 style   : 'height: 10em;'
						 });
					 });
					 <?php endif; ?>

					</script>

				</div>
	        </div>
	        <?php endforeach; ?>
	        
	        <?php $deleteStyle = isset($this->view->existingWatch) ? 'inline' : 'none' ?>
	        <?php $addStyle = $deleteStyle == 'inline' ? 'none' : 'inline' ?>
	        
	        <div class="note">
	        	<div class="padded">
		        <form id="issue-add-note-form" class="ajaxForm" method="post" action="<?php echo $createurl?>">
					<input type="hidden" name="_ajax" value="1" />
					<?php $this->view->requestValidator(); ?>
		            <input type="hidden" value="<?php echo $this->view->model->id?>" name="id"/>
		            <input type="hidden" value="<?php echo get_class($this->view->model)?>" name="attachedtotype"/>
		            <input type="hidden" value="<?php echo $this->view->model->id?>" name="attachedtoid"/>
		            <input type="hidden" value="<?php echo za()->getUser()->getUsername()?>" name="userid"/>
		            <div  class="wide-form">
		            <p>
		            <label for="note-title">Title:</label>
		            <input class="input" type="text" name="title" id="note-title" value="<?php $this->view->o($this->view->model->title)?>" />
		            </p>
		            <p>
		            <label for="note-note">Note:</label>
		            
		            <textarea name="note" rows="8" id="note-note"></textarea>
		            </p>
		            </div>
		            <p class="clear">
		                <input type="submit" class="abutton" value="Add Note" accesskey="a" />
		                <a title="Unsubscribe" style="display: <?php echo $deleteStyle?>;" id="delete-watch" href="#" onclick="$.post('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $this->view->model->id?>', type:'<?php echo get_class($this->view->model)?>'}, function() {$('#delete-watch').hide();$('#add-watch').show(); }); return false; "><img src="<?php echo resource('images/thumb_down.png')?>"/></a>
			        	<a title="Subscribe" style="display: <?php echo $addStyle?>;" id="add-watch" href="#" onclick="$.post('<?php echo build_url('note', 'addwatch')?>', {id:'<?php echo $this->view->model->id?>', type:'<?php echo get_class($this->view->model)?>'}, function() {$('#add-watch').hide();$('#delete-watch').show(); }); return false; "><img src="<?php echo resource('images/thumb_up.png')?>"/></a>
		            </p>
		            <p>
		            You can create automatic links to other Requests by typing "request (id)" where (id) is the id 
		            of the other request. 
		            </p>
		        </form>
		        </div>
	        </div>
	        
	        <?php if (count($userlist) && $this->view->u()->hasRole(User::ROLE_USER)): ?>
	        <div>
				<form action="<?php echo build_url('note', 'setwatchers')?>" class="ajaxForm" method="post">
					<input type="hidden" name="_ajax" value="1" />
					<input type="hidden" value="<?php echo get_class($this->view->model)?>" name="attachedtotype"/>
		            <input type="hidden" value="<?php echo $this->view->model->id?>" name="attachedtoid"/>
					<p>
					<label for="group-users">Users</label>
					<select name="watchusers[]" multiple="multiple" size="10" id="group-users">
					    <?php 
					    foreach ($userlist as $user): ?>
					    <option value="<?php echo $user->username?>" <?php echo isset($watchers[$user->username]) ? 'selected="selected"' : '';?>><?php echo $this->view->o($user->getUsername())?></option>
					    <?php endforeach; ?>
					</select>
					</p>
				
					<p>
					<input type="submit" class="abutton" value="Update Subscriptions" />
					</p>
				</form>
	        </div>
	        <?php endif; ?>
	    </div>
	    
	    <?php 
	}
}

?>