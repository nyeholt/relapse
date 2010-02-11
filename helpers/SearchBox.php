<?php
class Helper_SearchBox
{
    public function searchBox($size=24)
    {
        ?>
        <form class="inline" action="<?php echo build_url('search', 'index')?>">
            <input size="<?php echo $size?>" type="text" name="query" id="search-input" />
            <input type="submit" class="abutton" value="Search All" id="search-button" />
			<input type="submit" class="abutton" value="In Contacts" name="contacts" />
			<p style="font-size: small">

			</p>
        </form>
        <?php 
    }
}
?>