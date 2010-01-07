<?php

include_once APP_DIR.'/thirdparty/fckeditor/fckeditor.php';

/**
 * Shortcut to get the current user
 *
 */
class Helper_FCKEditor
{
	/**
	 * Get the current user
	 *
	 * @return  NovemberUser
	 */
	public function FCKEditor($name, $value='', $basePath = '')
	{
		$oFCKeditor = new FCKeditor($name);
        $oFCKeditor->BasePath = $basePath.'/';
        $oFCKeditor->Value = $value;
        $oFCKeditor->Create();
	}
}

?>