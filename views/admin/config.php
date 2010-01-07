<div id="parent-links">
    <a href="<?php echo build_url('admin', 'index')?>">Config</a>
    <a href="<?php echo build_url('admin', 'tracker')?>">Tracker</a>
	<a href="<?php echo build_url('admin', 'userlist')?>">User List</a>
	<a href="<?php echo build_url('admin', 'grouplist')?>">Group List</a>
	<a href="<?php echo build_url('leave', 'list')?>">Leave</a>
</div>

<h2>Configuration Settings</h2>

<form action="<?php echo build_url('admin', 'saveconfig')?>" method="post">
<table>
    <tr>
        <td width="300px">
            Application name
        </td>
        <td width="300px">
            <input class="input" type="text" value="<?php echo $this->config['name']?>" name="name" />
        </td>
    </tr>
    <tr>
        <td>
            Debug enabled
        </td>
        <td>
            On <input type="radio" value="1" name="debug" <?php echo ifset($this->config, 'debug', false) ? 'checked="checked"' : ''?> />
            Off <input type="radio" value="0" name="debug" <?php echo ifset($this->config, 'debug', false) ? '' : 'checked="checked"'?> />
        </td>
    </tr>
    <tr>
        <td>
            Query Log enabled
        </td>
        <td>
            On <input type="radio" value="1" name="log_queries" <?php echo ifset($this->config, 'log_queries', false) ? 'checked="checked"' : ''?> />
            Off <input type="radio" value="0" name="log_queries" <?php echo ifset($this->config, 'log_queries', false) ? '' : 'checked="checked"'?> />
        </td>
    </tr>
    <tr>
        <td>
            From email address<br/>
            <em>This is email address people receive system notifications from</em>
        </td>
        <td>
            <input class="input" type="text" value="<?php echo $this->config['from_email']?>" name="from_email" />
        </td>
    </tr>
    <tr>
        <td>
            SMTP Server<br/>
            <em>This is the mail outbound server address, leave blank for the default PHP setting</em>
        </td>
        <td>
            <input class="input" type="text" value="<?php echo $this->config['smtp_server']?>" name="smtp_server" />
        </td>
    </tr>
    <tr>
        <td>
            Theme<br/>
            <em>Check your "Themes" folder for valid options</em>
        </td>
        <td>
            <input class="input" type="text" value="<?php echo ifset($this->config, 'theme')?>" name="theme" />
        </td>
    </tr>
    <?php $this->getMods($this, 'admin-config');?>
    <tr>
        <td>
        </td>
        <td>
            <input class="abutton" type="submit" value="Update Config" />
        </td>
    </tr>
    <!--
    <tr>
        <td>
        </td>
        <td>
        </td>
    </tr>
    -->
</table>
</form>
