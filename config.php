<?php

require_once(INCLUDE_DIR.'/class.plugin.php');
require_once(INCLUDE_DIR.'/class.forms.php');
class StockConfig extends PluginConfig{
    function getOptions() {
		$form_choices = array('0' => '--None--');
        foreach (DynamicForm::objects()->filter(array('type'=>'G')) as $group)
        {
            $form_choices[$group->get('id')] = $group->get('title');
        }
        return array(
            'stock_backend_enable' => new BooleanField(array(
                'id'    => 'stock_backend_enable',
                'label' => 'Enable Backend',
                'configuration' => array(
                    'desc' => 'Staff backend interface')                
            )),
            'stock_frontend_enable' => new BooleanField(array(
                'id'    => 'stock_frontend_enable',
                'label' => 'Enable Frontend',
                 'configuration' => array(
                    'desc' => 'Client facing interface')  
            )),
            'stock_custom_form' => new ChoiceField(array(
                'id'    => 'stock_custom_form',
                'label' => 'Custom Form Name',
                'choices' => $form_choices,
                 'configuration' => array(
                    'desc' => 'Custom form to use for stock')  
            )),
                       
		);
    }
    
    function pre_save(&$config, &$errors) {
        global $msg;
        if (!$errors)
            $msg = 'OK';
        return true;
    }
}