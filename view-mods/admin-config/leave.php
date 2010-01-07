	<tr>
        <td>
            <p>Yearly number of days leave</p>
            <em>The system will automatically calculate how much <br/>leave a user has based on this value, and any bonus leave they've been given</em>
        </td>
        <td>
            <input class="input" type="text" value="<?php echo ifset($view->config, 'days_leave', 20)?>" name="days_leave" />
        </td>
    </tr>
	<tr>
        <td>
            <p>Approvers</p>
            <em>A comma separated list of usernames; these approvers will be notified of leave and expense applications</em>
        </td>
        <td>
            <input class="input" type="text" value="<?php echo ifset($view->config, 'leave_approvers')?>" name="leave_approvers" />
        </td>
    </tr>