<?php
class Helper_PaneSearchBox
{
    public function paneSearchBox($size=24)
    {
        ?>
	<form class="inline" action="<?php echo build_url('search', 'index')?>" id="QuickSearch">
            <input size="<?php echo $size?>" type="text" name="query" id="search-input" />
            <input type="submit" class="abutton" value="Search" />
			<input type="checkbox" name="contacts" value="true" id="ContactSearch"/><label for="ContactSearch">Contacts Only</label>
        </form>
        <?php 
    }
}
?>