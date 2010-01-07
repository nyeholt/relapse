
TimeTable = function (id, startInput, endInput, horizontal, selectable)
{
	this.id = id;
	
	// Stick "this" into the div
	this.enableSelection = true;
	if (selectable != null) {
		this.enableSelection = selectable;
	}
	this.startInput = startInput;
	this.endInput = endInput;
	this.table = null;
	this.tbody = null;
	this.horizontal = horizontal == null ? true : horizontal;
	this.table = TimeTable.createElement("table");
	
	this.enforceLockedSessions = true;
	
	this.slots = new Array();
	
	this.startTime = null;
	this.endTime = null;
	
	// What's currently selected in this table? 
	
	this.startTime = null;
	this.endTime = null;
	
	// Other option is 'start'
	this.lastSelected = 'end';
	
	this.incrementSize = 1;
	
}

/**
 * Set a bunch of sessions to be locked off
 * and not usable
 */
TimeTable.prototype.setSessions = function()
{
	for (var i=0; i<arguments.length; i++) {
		var session = arguments[i];
		// Block out a session
		this.lockSession(session.start, session.end, session.color, session.tooltip);
	}
	this.redraw();
}

TimeTable.prototype.lockSession = function(start, end, color, tooltip)
{
	// The slots array is indexed by the slot's
	// time value, so use that for choosing what to lock
	for (var time in this.slots) {
		if (time >= start && time < end) {
			this.slots[time].isLocked = true;
			if (color != null) {
				this.slots[time].slotColor = color;
			}
			if (tooltip != null) {
				this.slots[time].tooltip = tooltip;
			}
		}
	}
}

TimeTable.prototype.setStartTime = function(time)
{
	this.startTime = time;
	// gotta convert the time to h:m am/pm
	jQuery("#"+this.startInput).val(TimeTable.convertTime(time));
	jQuery('#'+this.endInput).val('');
}

TimeTable.prototype.setEndTime = function(time)
{
	// the endtime identifies the slot, but for the actual
	// time set in the form, we need the END of the slot, 
	// or endTime + this.incrementSize
	this.endTime = time;
	// gotta convert the time to h:m am/pm
	time += this.incrementSize;
	jQuery("#"+this.endInput).val(TimeTable.convertTime(time));
}

TimeTable.prototype.create = function(start, end, increment, hilite) {
	var totalHours = end - start;
	var slots = totalHours / increment;
	
	
	this.tbody = TimeTable.createElement("tbody");
	this.table.appendChild(this.tbody);
	
	if (this.horizontal) {
		this.createColumns(start, end, increment, hilite);
	} else {
		this.createRows(start, end, increment, hilite);
	}
	
	jQuery('#'+this.id).append(this.table);
}

TimeTable.prototype.clearSelection = function() 
{
	for (var i in this.slots) {
		this.slots[i].isSelected = false;
	}
	this.startTime = null;
	this.endTime = null;
	this.lastSelected = 'end';
}


TimeTable.prototype.clicked = function()
{
	if (!this.enableSelection) return;
	
	if (this.isLocked && this.timeTable.enforceLockedSessions) {
		return;
	}

	if (this.timeTable.lastSelected == 'end') {
		// Clear all other selections
		this.timeTable.clearSelection();
		this.isSelected = true;
		
		// Set the selection as the table's start point
		this.timeTable.setStartTime(this.timeValue);
		
		this.timeTable.lastSelected = 'start';
	} else {
		// If the user selected before the start time
		// we'll take it as a sign that they want to 
		// clear selections
		if (this.timeValue < this.timeTable.startTime) {
			this.timeTable.clearSelection();
		} else {
			// We're selecting the end point, so lets
			// just make sure that there's no booking
			// between the start and end points
			
			for (var timeVal in this.timeTable.slots) {
				// if the slot has a timeval greater than
				// the start time, we need to check it
				if (timeVal > this.timeTable.startTime && timeVal <= this.timeValue) {
					var timeSlot = this.timeTable.slots[timeVal];
					if (timeSlot.isLocked && this.timeTable.enforceLockedSessions) {
						break;
					} else {
						timeSlot.isSelected = true;
					}
				}
			}
			
			this.timeTable.setEndTime(this.timeValue);
		}
		// Mark the selection
		this.timeTable.lastSelected = 'end';
	}
	
	this.timeTable.redraw();
}

TimeTable.prototype.redraw = function()
{
	// If the cell's between the start and end time, then 
	// make sure it's selected. 
	for (var k in this.slots) {
		var cell = this.slots[k];
		try {
			jQuery(cell).removeClass("selected-slot");
			jQuery(cell).removeClass("locked-slot");
		} catch (err) {
			// IE is a bit silly sometimes and will make 'className' null if
			// you remove too many classes... so we'll just allow that to 
			// happen and keep going
		}
		if (cell.isSelected) {
			jQuery(cell).addClass("selected-slot");
		} else if (cell.isLocked) {
			jQuery(cell).addClass("locked-slot")
		}
		if (cell.slotColor != null) {
			jQuery(cell).css("background-color", cell.slotColor);
		} else {
			jQuery(cell).css("background-color", "none");
		}
		
		if (cell.tooltip != null) {
			jQuery(cell).attr("title", cell.tooltip);
		} else {
			jQuery(cell).attr("title", "");
		}
	}
	
	if (!this.enableSelection) return;
	jQuery("#"+this.id).Selectable (
    	{
    	    accept: 'time-segment', 
    	    selectedclass: 'selected-slot',
    	    opacity: 0.2,
    		helperclass : 'selecthelper',
    		onselect : function(serial)
    		{
    		    // Get the first and last selected
    		    if (serial.o != null && serial.o.length > 0) {
    		        this.el[0].timeTable.setStartTime(parseFloat(serial.o[0].replace(/timesegment/, "")));
    		        this.el[0].timeTable.setEndTime(parseFloat(serial.o[serial.o.length - 1].replace(/timesegment/, "")));
    		    }
    		}
    	}
	);
}


TimeTable.prototype.createColumns = function(start, end, increment, hilite)
{
	var td;
	if (increment != null) {
		this.incrementSize = increment;
	}
	// If highlighting blocks, then create a row
	// which spans the actual time segments
	if (hilite > 0) {
		tr = TimeTable.createElement("tr");
		this.tbody.appendChild(tr);
		// For the numbers, we only go up to the
		// 1 before the end so we don't create
		// extraneous columns
		for (var i=start; i < end; i += increment) {
			if (i % hilite == 0) {
				td = TimeTable.createElement('td');
				td.setAttribute("colspan", hilite / increment);
				td.colSpan = hilite / increment;
				td.className = "time-numbers";
				td.innerHTML = i +' - '+(i+hilite);
				tr.appendChild(td);
				
			}
		}
	}

	var tr2 = TimeTable.createElement("tr");
	this.tbody.appendChild(tr2);
	
	for (var i=start; i < end; i += increment) {
		td = TimeTable.createElement('td');
		td.innerHTML = '';
		td.timeValue = i;
		td.id = ("timesegment"+i);
		td.timeTable = this;
		// Save the element so that later on it can be
		// used to make largescale selections. 
		this.slots[i] = td;
		jQuery(td).addClass("time-segment");
		// jQuery(td).click(this.clicked);
		tr2.appendChild(td);
	}
}


TimeTable.prototype.createRows = function(start, end, increment, hilite)
{
	for (var i=start; i < end; i += increment) {
		var tr = TimeTable.createElement("tr");
		this.tbody.appendChild(tr);
	
		
		var td = TimeTable.createElement('td');
		if (i % hilite == 0) {
			td.className += "time";
			td.innerHTML = i;
		} else {
			td.innerHTML = '&nbsp;';
		}
		tr.appendChild(td);
	}
}


TimeTable.createElement = function(type, parent) {
	var el = null;
	if (document.createElementNS) {
		// use the XHTML namespace; IE won't normally get here unless
		// _they_ "fix" the DOM2 implementation.
		el = document.createElementNS("http://www.w3.org/1999/xhtml", type);
	} else {
		el = document.createElement(type);
	}
	if (typeof parent != "undefined") {
		parent.appendChild(el);
	}
	return el;
};

/**
 * Converts an hour to a time based on format
 */
TimeTable.convertTime = function(value, format)
{
	var mins = value % 1;
	var hours = value - mins;
	mins *= 60;
	var ap = 'am';
	if (mins < 10) mins = '0'+mins;
	
	return hours+':'+mins;
}
