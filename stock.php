<?php

require_once ('config.php');

define ('STOCK_PLUGIN_VERSION', '0.1');

define ('STOCK_TABLE', TABLE_PREFIX . 'STOCK');
define ('STOCK_CATEGORY_TABLE', TABLE_PREFIX . 'STOCK_category');
define ('STOCK_STATUS_TABLE', TABLE_PREFIX . 'STOCK_status');
define ('STOCK_TICKET_TABLE', TABLE_PREFIX . 'STOCK_ticket');
define ('STOCK_CONFIG_TABLE', TABLE_PREFIX . 'STOCK_config');
define ('STOCK_TICKET_RECURRING__TABLE', TABLE_PREFIX . 'STOCK_ticket_recurring');
define ('STOCK_TICKET_VIEW', TABLE_PREFIX . 'STOCKTicketView');
define ('STOCK_SEARCH_VIEW', TABLE_PREFIX . 'STOCKSearchView');
define ('STOCK_FORM_VIEW', TABLE_PREFIX . 'STOCKFormView');

define ('STOCK_DELETE_TRIGGER', TABLE_PREFIX . 'STOCK_ADEL');
define ('STOCK_INSERT_TRIGGER', TABLE_PREFIX . 'STOCK_AINS');
define ('STOCK_UPDATE_TRIGGER', TABLE_PREFIX . 'STOCK_AUPD');
define ('STATUS_INSERT_TRIGGER', TABLE_PREFIX . 'STOCK_status_AINS');
define ('STATUS_UPDATE_TRIGGER', TABLE_PREFIX . 'STOCK_status_AUPD');
define ('STATUS_DELETE_TRIGGER', TABLE_PREFIX . 'STOCK_status_ADEL');

define ('EVENT_DELETE_TRIGGER', TABLE_PREFIX . 'ticket_event_AINS');
define ('EVENT_UPDATE_TRIGGER', TABLE_PREFIX . 'ticket_event_AUPD');

define ('CREATE_FORM_FIELDS_PROCEEDURE', TABLE_PREFIX . 'CreateSTOCKFormFields');
define ('COPY_FORM_ENTRY_PROCEEDURE', TABLE_PREFIX . 'STOCK_Copy_Form_Entry');
define ('REOPEN_TICKET_PROCEEDURE', TABLE_PREFIX . 'STOCK_Reopen_Ticket');
define ('CRON_PROCEEDURE', TABLE_PREFIX . 'STOCKCronProc');

define ('OST_WEB_ROOT', osTicket::get_root_path ( __DIR__ ) );

define ('STOCK_WEB_ROOT', OST_WEB_ROOT . 'scp/dispatcher.php/STOCK/');

define ('OST_ROOT', INCLUDE_DIR . '../');

define ('PLUGINS_ROOT', INCLUDE_DIR . 'plugins/');

define ('STOCK_PLUGIN_ROOT', __DIR__ . '/');
define ('STOCK_INCLUDE_DIR', STOCK_PLUGIN_ROOT . 'include/');
define ('STOCK_MODEL_DIR', STOCK_INCLUDE_DIR . 'model/');
define ('STOCK_CONTROLLER_DIR', STOCK_INCLUDE_DIR . 'controller/');

define ('STOCK_APP_DIR', STOCK_PLUGIN_ROOT . 'app/');
define ('STOCK_ASSETS_DIR', STOCK_PLUGIN_ROOT . 'assets/');
define ('STOCK_VENDOR_DIR', STOCK_PLUGIN_ROOT . 'vendor/');
define ('STOCK_VIEWS_DIR', STOCK_PLUGIN_ROOT . 'views/');
define ('STOCK_STAFFINC_DIR', STOCK_INCLUDE_DIR . 'staff/');
define ('STOCK_CLIENTINC_DIR', STOCK_INCLUDE_DIR . 'client/');

class StockPlugin extends Plugin {
	var $config_class = 'StockConfig';
	
	public static function getCustomForm(){
		$sql = 'SELECT id FROM ' . PLUGIN_TABLE . ' WHERE name=\'STOCK Manager\'';
		$res = db_query ( $sql );
		if (isset ( $res )){
			$ht = db_fetch_array ( $res );
			$config = new STOCKConfig ( $ht ['id'] );
			return $config->get ('STOCK_custom_form');
		}
		return false;
	}
	function bootstrap(){
		if ($this->firstRun ()){
			if (! $this->configureFirstRun ()){
				return false;
			}
		}		
		else if ($this->needUpgrade ()){
			$this->configureUpgrade ();
		}
		$config = $this->getConfig ();
		if ($config->get ('STOCK_backend_enable')){
			$this->createStaffMenu ();
		}
		if ($config->get ('STOCK_frontend_enable')){
			$this->createFrontMenu ();
		}
		Signal::connect ('apps.scp', array (
				'StockPlugin',
				'callbackDispatch' 
		) );
	}

}