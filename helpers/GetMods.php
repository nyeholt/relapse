<?php

/**
 * Includes all of the files defined in mods/modname into the
 * current view. This allows custom functionality to be hooked
 * into the application. 
 *
 */
class Helper_GetMods
{
    public function getMods($view, $modName)
    {
        $modDir = APP_DIR.DIRECTORY_SEPARATOR.'view-mods'.DIRECTORY_SEPARATOR.$modName;
        if (is_dir($modDir)) {
            $dir = new DirectoryIterator($modDir);
            
            foreach ($dir as $value) {
                $dirname = $value->__toString();
                if ($dirname == '.' || $dirname == '..') continue;
                if (is_dir($modDir.DIRECTORY_SEPARATOR.$dirname)) {
                    continue;
                }
                
                include $modDir.DIRECTORY_SEPARATOR.$dirname;
            }
        }
    }
}
?>