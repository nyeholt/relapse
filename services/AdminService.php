<?php

class AdminService
{
    /**
     * The database service
     *
     * @var DbService
     */
    public $dbService;
    
    public function getSystemConfig()
    {
        // We're going to load up only that which is defined in 
        // the user editable configuration. 
        include APP_DIR.'/'.NovemberApplication::SYSTEM_CONFIG;
        return $user_config;
    }
    
    public function saveConfig($newConfig)
    {
        // Open the file and write the config back into it. 
        $fp = fopen(APP_DIR.'/'.NovemberApplication::SYSTEM_CONFIG, "w");
        $conf = var_export($newConfig, true);
        
        $conf = '<?php
$user_config = array(); // new array!
$user_config = '.$conf.'; ?>';
        if ($fp) {
            fputs($fp, $conf);
            fclose($fp);
        }
    }
}

?>