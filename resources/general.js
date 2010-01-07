
function displayItem(type, id)
{
    // Hide all current blocks
    $(".hidden-info").hide();
    var target = $('#display-target');
    var infoDiv = $('#'+type+'-info-'+id);
    target.append(infoDiv);
    infoDiv.show();
}

function showContacts(id)
{
    var contactDiv = $('#client-info-'+id+'-contacts');
    //if (contactDiv.html() == '') {
    // Load the contacts into that div.
        contactDiv.load(CONTACT_LOAD_URL+'clientid/'+id);
    //}
}

function loadClientData(id, type, action)
{
    var targetDiv = $('#client-info-'+id+'-'+type);
    //if (targetDiv.html() == '') {
        targetDiv.load(action+'clientid/'+id);
    //}
}

function loadProjectData(id, type, action)
{
    var targetDiv = $('#project-info-'+id+'-'+type);
    //if (targetDiv.html() == '') {
    targetDiv.load(action+'projectid/'+id);
    //}
}

/**
* Loads the timesheet details for a given task id
*/
function loadTaskDetail(id, url)
{
	var tgt = $("#task-"+id+"-timesheet-detail");
	
	tgt.load(url);
}

function showInfo(sourceId, remote)
{
	if (remote) {
		$("#info-container").load(sourceId);
	} else {
		$("#info-container").html($('#'+sourceId).html());
	}
	
	$("#info-container").show();
}

function hideInfo()
{
	$("#info-container").hide();
}

function popup(url, name, width, height)
{
	window.open(url, name, "width="+width+",height="+height+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,resizable=1")
}

function addNote(subject, id, type)
{
    $('#attachedtotype').val(type);
    $('#attachedtoid').val(id);
    $('#noteuserid').val(CURRENT_USER_ID);
    
    $('#notes-container').hide();
    $('#add-note').show();
    $('#add-note-title').val('RE: '+subject);
    $('#add-note-note').val('').focus();
    $('#add-note input').attr('disabled', '');
}

function viewNotes(id, type)
{
    $.get(NOTES_URL, {id: id, type: type}, function(data) {
       $('#notes-content').html(data);
       $('#add-note').hide();
       $('#notes-container').show();
    });
}

function initialiseFeatureSorting(id)
{
    
    // Once loaded, we need to initialise the draggability of what we've loaded. 
    $('#feature-'+id+'-list').Sortable (
        {
            accept: 'feature-'+id+'-item',
            helperclass: 'sortHelper',
            activeclass : 	'sortableactive',
			hoverclass : 	'sortablehover',
			onChange : function(ser)
			{
			},
			onStart : function()
			{
				
			},
			onStop : function()
			{
				// $.iAutoscroller.stop();
				serial = $.SortSerialize('feature-'+id+'-list');
	            // alert(serial.hash);
			}
        }
    );
}

function displayTaskTimesheet(trigger, taskId, loadUrl)
{
	$('.timesheet-div').hide();
	
	// find the timesheet and pop it up into a new placed div
	var timesheetDiv = $('#task-'+taskId+'-timesheet');
	// Load the data for it
	$('#task-'+taskId+'-timesheet-detail').load(loadUrl+'taskid/'+taskId);

	var loc = $(trigger).position();

	timesheetDiv.css("position", "absolute");
	timesheetDiv.css("top", loc.top+"px");
	timesheetDiv.css("left", (loc.left - 600)+"px");

	timesheetDiv.show();
	
	return false;
}

function displayProjectTree(trigger, itemId, itemType, loadUrl)
{
	$('.project-tree').hide();
	
	// find the timesheet and pop it up into a new placed div
	var treeDiv = $('#project-'+itemId+'-tree');
	// Load the data for it
	treeDiv.load(loadUrl+'id/'+itemId+'/type/'+itemType);

	var loc = $(trigger).position();

	treeDiv.css("position", "absolute");
	treeDiv.css("top", "120px");
	treeDiv.css("left", "200px");

	treeDiv.show();
	
	return false;
}
