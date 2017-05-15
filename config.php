<?php


class StockConfig extends PluginConfig{
    function getOptions() {
 
    }
    
    function pre_save(&$config, &$errors) {
        global $msg;
        if (!$errors)
            $msg = 'OK';
        return true;
    }
}