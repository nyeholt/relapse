
<div id="issue-list-container">
	<?php 
	// Figure out urls for posting stuff too. 
	// $url = the url for adding an issue
	// $searchUrl = the url for filtering issues
	$url = '';
	$searchUrl = '';
	
	if (isset($this->model)) {
		// either client or project 
		switch (mb_strtolower(get_class($this->model))) {
			case 'project': {
				$searchUrl = build_url('issue', 'projectList', array('projectid'=>$this->model->id, '__ajax' => '1'));
				break;
			}
			case 'client': {
				$searchUrl = build_url('issue', 'clientList', array('clientid'=>$this->model->id, '__ajax' => '1'));
				break;
			}
			default: {
				break;
			}
		}
	} else {
		$searchUrl = build_url('issue', 'index', array('__ajax' => '1'));
	}
	
	if (isset($this->attachedToType)) {
	    $url = build_url('issue', 'edit', array($this->attachedToType => $this->model->id));
	} else {
	    $url = build_url('issue', 'edit');
	}

	?>

	<?php if (!$this->minimal): ?>
	<h2>Requests</h2>
	<?php endif; ?>
	[<a href="#" onclick="$('#issue-filter-form').toggle(); return false;">Filter</a>]
	
	[<a href="<?php echo build_url('issue', 'csvExport', array('unlimited' => 1)).'?'.encode_params($this->searchParams, '&','=')?>" target="_blank">Export</a>]
	
	<form method="get" action="<?php echo $searchUrl?>" id="issue-filter-form" style="display: none;">
	<table>
		<thead>
			<tr>
				<th>Title</th>
				<th>Status</th>
				<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
				<th>Client</th>
				<?php endif; ?>
				<th>Severity</th>
				<th>Type</th>
				<th>Start / End Updated</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="vertical-align: top">
					<input type="text" name="titletext"></input>
				</td>
				<td style="vertical-align: top">
					<select name="status[]" style="width: 8em" multiple="multiple">
						<?php foreach ($this->statuses as $item): ?>
						<option value="<?php $this->o($item)?>"><?php $this->o($item)?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
				<td style="vertical-align: top">
					<select name="clientid" style="width: 8em">
						<option value=""></option>
						<?php foreach ($this->clients as $item): ?>
						<option value="<?php $this->o($item->id)?>"><?php $this->o($item->title)?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<?php endif; ?>
				<td style="vertical-align: top">
					<select name="severity" style="width: 8em">
						<option value=""></option>
						<?php foreach ($this->severities as $item): ?>
						<option value="<?php $this->o($item)?>"><?php $this->o($item)?></option>
						<?php endforeach; ?>
					</select>
				</td>
				
				<td style="vertical-align: top">
					<select name="type" style="width: 8em">
						<option value=""></option>
						<?php foreach ($this->types as $item): ?>
						<option value="<?php $this->o($item)?>"><?php $this->o($item)?></option>
						<?php endforeach; ?>
					</select>
				</td>
				
				<td style="vertical-align: top">
					<input size="7" readonly="readonly" type="text" name="startdate" id="updatestartdate"/>
					<input size="7" readonly="readonly" type="text" name="enddate" id="updateenddate" />
					
					<?php 
					$this->calendar('updatestartdate');
					$this->calendar('updateenddate');
					?>
				</td>
				<td style="vertical-align: top">
				<label class="normal-label" for="mine-only">Mine Only</label>
					 <input id="mine-only" type="checkbox" value="1" name="mineOnly" />
					 <input type="submit" value="Filter" class="abutton" />
					 <input type="button" value="Reset" class="abutton" onclick="location.href=location.href;" />
				</td>
			</tr>
		</tbody>
		</table>
	</form>
	<br/>
	<table class="item-table" cellpadding="0" cellspacing="0">
	    <thead>
	    <tr>
	    	<th><?php $this->sortHeader('ID', 'id', $searchUrl, 'sort', 'dir', true)?></th>
	        <th width="25%"><?php $this->sortHeader('Title', 'title', $searchUrl, 'sort', 'dir', true)?></th>
	        <?php if (!$this->minimal): ?>
	        <th><?php $this->sortHeader('Client', 'clientname', $searchUrl, 'sort', 'dir', true)?></th>
	        <?php endif; ?>
	        <th><?php $this->sortHeader('Project', 'projectname', $searchUrl, 'sort', 'dir', true)?></th>
   	        <!-- <th><?php // $this->sortHeader('Category', 'category', $searchUrl, 'sort', 'dir', true)?></th> -->
   	        <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
   	        <th><?php $this->sortHeader('Ela', 'elapsed', $searchUrl, 'sort', 'dir', true)?></th>
   	        <th><?php $this->sortHeader('Est', 'estimated', $searchUrl, 'sort', 'dir', true)?></th>
   	        <?php endif; ?>
	        <th><?php $this->sortHeader('Status', 'status', $searchUrl, 'sort', 'dir', true)?></th>
	        <th><?php $this->sortHeader('Severity', 'severity', $searchUrl, 'sort', 'dir', true)?></th>
	        <th><?php $this->sortHeader('Type', 'issuetype', $searchUrl, 'sort', 'dir', true)?></th>
	        <th><?php $this->sortHeader('Creator', 'creator', $searchUrl, 'sort', 'dir', true)?></th>
	        <th><?php $this->sortHeader('Consultant', 'userid', $searchUrl, 'sort', 'dir', true)?></th>
	        <th><?php $this->sortHeader('Last Updated', 'updated', $searchUrl, 'sort', 'dir', true)?></th>
	
	        <th width="5%">&nbsp;</th>
	    </tr>
	    </thead>
	    <tbody>
	    <?php $index=0; foreach ($this->issues as $issue): ?>
	    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?> status-<?php $this->o(mb_strtolower(str_replace(' ', '-', $issue->status)));?>">
	        <td><?php $this->o($issue->id)?></td>
	        <td>
	        <a href="<?php echo build_url('issue', 'edit', array('id'=>$issue->id))?>"><?php $this->o($this->ellipsis($issue->title, 40));?></a>
	        </td>
	        <?php if (!$this->minimal): ?>
	        <td><a href="<?php echo build_url('client', 'view', array('id'=>$issue->clientid))?>" title="<?php $this->o($issue->clientname);?>"><?php $this->o($this->ellipsis($issue->clientname));?></a></td>
	        <?php endif; ?>
	        <td>
	        <?php if ($this->u()->hasRole(User::ROLE_USER) || !$issue->privateproject): ?>
	        <a href="<?php echo build_url('project', 'view', array('id'=>$issue->projectid))?>" title="<?php $this->o($issue->projectname);?>"><?php $this->o($issue->projectid.': '.$this->ellipsis($issue->projectname));?></a>
	        <?php endif; ?>
	       	</td>
			
				        
	        <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
	        <td><?php $this->o(sprintf('%.2f', $issue->elapsed));?></td>
	        <td><?php $this->o(sprintf('%.2f', $issue->estimated));?></td>
	        <?php endif; ?>
	        <!-- <td><?php $this->o($issue->category);?></td> -->
	        <td style="text-align: center"><?php $this->o($issue->status);?></td>
	        <td style="text-align: center"><?php $this->o($issue->severity);?></td>
	        <td style="text-align: center"><?php $this->o($issue->issuetype);?></td>
	        <td style="text-align: center"><?php $this->o($issue->creator)?></td>
	        <td style="text-align: center"><?php $this->o($issue->userid)?></td>
	        <td style="text-align: center"><?php $this->o(date('Y-m-d H:i:s', strtotime($issue->updated)));?></td>
	        <td>
	            <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
	            <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('issue', 'delete', array('id'=>$issue->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
	            <?php endif; ?>
	        </td>
	    </tr>
	    <?php endforeach; ?>
	    </tbody>
	</table>
	
	<?php if (!$this->minimal): ?>
	<p>
	<a class="abutton" href="<?php echo $url?>">Create Request</a>
	</p>
	<?php endif; ?>
	
	<script type="text/javascript">
		$().ready(function() {
			loadIssueList();
		});
		
		function loadIssueList()
		{
			var issueIndex = $("#issues-index");
			if (issueIndex) {
				issueIndex.html("Requests (<?php echo $this->totalCount ? $this->totalCount : count($this->issues)?>)");
			}
			bindIssueSortingLinks();
		}

		function bindIssueSortingLinks()
       	{
       		$('#issue-filter-form').ajaxForm({'target': '#issue-list-container'});
       		$('.ajax-sort-header').click(function() {
       			var target = $(this).attr('target')+'&__ajax=1';
       			$.get(target, processSortingData);
       			return false;
       		});
       	}
       	
       	function processSortingData(data) {
       		$('#issue-list-container').html(data);
       		bindIssueSortingLinks();
       	}

	</script>

	<?php if ($this->listSize) $this->ajaxPager('issue-list-container', $this->totalCount, $this->listSize, $this->pagerName, $searchUrl, array(), true); ?>
	<?php // if ($this->listSize) $this->pager($this->totalCount, $this->listSize, $this->pagerName, array(), true); ?>
	
	<h4><a href="#" onclick="$('#request-status-key').toggle(); return false">Request Status Key</a></h4>
	<ul id="request-status-key" style="display: none;">
		<li class="status-new">New - The request has been entered in the system</li>
		<li class="status-open">Open - The request has been picked up by a Lateral Minds technician</li>
		<li class="status-in-progress">In Progress - The request is being actively acted upon</li>
		<li class="status-on-hold">On Hold - The request is still open, but no work will be done for the moment</li>
		<li class="status-pending">Pending - The request has been completed by Lateral Minds, but the change is still pending review by the client</li>
		<li class="status-resolved">Resolved - The request has been completed</li>
		<li class="status-closed">Closed - The request has been closed due to not requiring any further resolution</li>
	</ul>
</div>

