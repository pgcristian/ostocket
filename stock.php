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



define ('EVENT_DELETE_TRIGGER', TABLE_PREFIX . 'ticket_event_AINS');
define ('EVENT_UPDATE_TRIGGER', TABLE_PREFIX . 'ticket_event_AUPD');

define ('CREATE_FORM_FIELDS_PROCEEDURE', TABLE_PREFIX . 'CreateSTOCKFormFields');
define ('COPY_FORM_ENTRY_PROCEEDURE', TABLE_PREFIX . 'STOCK_Copy_Form_Entry');
define ('REOPEN_TICKET_PROCEEDURE', TABLE_PREFIX . 'STOCK_Reopen_Ticket');
define ('CRON_PROCEEDURE', TABLE_PREFIX . 'STOCKCronProc');

define ('OST_WEB_ROOT', osTicket::get_root_path ( __DIR__ ) );



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

require_once (STOCK_VENDOR_DIR . 'autoload.php');
spl_autoload_register ( array (
		'STOCKPlugin',
		'autoload' 
) );

class StockPlugin extends Plugin {
	var $config_class = 'StockConfig';
	public static function autoload($className){
		$className = ltrim ( $className, '\\');
		$fileName = '';
		$namespace = '';
		if ($lastNsPos = strrpos ( $className, '\\')){
			$namespace = substr ( $className, 0, $lastNsPos );
			$className = substr ( $className, $lastNsPos + 1 );
			$fileName = str_replace ('\\', DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace ('_', DIRECTORY_SEPARATOR, $className ) . '.php';
		$fileName = 'include/' . $fileName;
		
		if (file_exists ( STOCK_PLUGIN_ROOT . $fileName )){
			require $fileName;
		}
	}
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
	static public function callbackDispatch($object, $data){
		$search_url = url ('^/STOCK.*search', patterns ('controller\STOCKItem', url_post ('^.*', 'searchAction') ) );
		$categories_url = url ('^/STOCK.*categories/', patterns ('controller\STOCKCategory', url_get ('^list$', 'listAction'), url_get ('^listJson$', 'listJsonAction'), url_get ('^listJsonTree$', 'listJsonTreeAction'), url_get ('^view/(?P<id>\d+)$', 'viewAction'), url_get ('^openTicketsJson/(?P<item_id>\d+)$', 'openTicketsJsonAction'), url_get ('^closedTicketsJson/(?P<item_id>\d+)$', 'closedTicketsJsonAction'), url_get ('^getItemsJson/(?P<category_id>\d+)$', 'categoryItemsJsonAction'), url_post ('^save', 'saveAction'), url_post ('^delete', 'deleteAction') ) );
		$item_url = url ('^/STOCK.*item/', patterns ('controller\STOCKItem', url_get ('^list$', 'listAction'), url_get ('^listJson$', 'listJsonAction'), url_get ('^listBelongingJson$', 'listBelongingJsonAction'), url_get ('^listNotBelongingJson$', 'listNotBelongingJsonAction'), url_get ('^listStaffJson$', 'listStaffJsonAction'), url_get ('^view/(?P<id>\d+)$', 'viewAction'), url_get ('^new/(?P<category_id>\d+)$', 'newAction'), url_post ('^publish', 'publishAction'), url_post ('^activate', 'activateAction'), url_post ('^save', 'saveAction'), url_get ('^openTicketsJson/(?P<item_id>\d+)$', 'openTicketsJsonAction'), url_get ('^closedTicketsJson/(?P<item_id>\d+)$', 'closedTicketsJsonAction'), url_get ('^getDynamicForm/(?P<id>\d+)$', 'getDynamicForm'), url_post ('^search', 'searchAction'), url_post ('^delete', 'deleteAction'), url_post ('^openNewTicket', 'openNewTicketAction') ) );
		$status_url = url ('^/STOCK.*status/', patterns ('controller\STOCKStatus', url_get ('^list$', 'listAction'), url_get ('^view/(?P<id>\d+)$', 'viewAction'), url_get ('^new/(?P<category_id>\d+)$', 'newAction'), url_get ('^listJson$', 'listJsonAction'), url_get ('^getItemsJson/(?P<status_id>\d+)$', 'statusItemsJsonAction'), url_post ('^save', 'saveAction'), url_post ('^delete', 'deleteAction') ) );
		$recurring_url = url ('^/STOCK.*recurring/', patterns ('controller\TicketRecurring', url_get ('^list$', 'listAction'), url_get ('^view/(?P<id>\d+)$', 'viewAction'), url_get ('^viewByTicket/(?P<id>\d+)$', 'viewByTicketAction'), url_get ('^addByTicket/(?P<id>\d+)$', 'addByTicketAction'), url_get ('^new/(?P<category_id>\d+)$', 'newAction'), url_get ('^listJson$', 'listJsonAction'), url_get ('^getItemsJson/(?P<status_id>\d+)$', 'statusItemsJsonAction'), url_get ('^listTicketsJson$', 'listTicketsJson'), url_get ('^listSTOCKJson$', 'listSTOCKJson'), url_post ('^save', 'saveAction'), url_post ('^delete', 'deleteAction'), url_post ('^enableEvents', 'enableEventsAction') ) );
		$maintenance_url = url ('^/STOCK.*maintenance/', patterns ('controller\Maintenance', url_get ('^startStructureTest$', 'startDatabaseIntegrityTest'), url_get ('^purgeData$', 'startDatabaseDataPurge'), url_get ('^recreateDatabase', 'startDatabaseRecreate'), url_get ('.*', 'defaultAction') ) );
		$media_url = url ('^/STOCK.*assets/', patterns ('controller\MediaController', url_get ('^(?P<url>.*)$', 'defaultAction') ) );
		$dashboard_url = url ('^/STOCK.*dashboard/', patterns ('controller\Dashboard', url_get ('^treeJson', 'treeJsonAction'), url_get ('.*', 'viewAction') ) );
		$redirect_url = url ('^/STOCK.*ostroot/', patterns ('controller\MediaController', url_get ('^(?P<url>.*)$', 'redirectAction') ) );
	}
	function createDBTables(){
		$installer = new \util\STOCKInstaller ();
		return $installer->install ();
	}
	function configureFirstRun(){
		if (! $this->createDBTables ()){
			echo "Configuration error of first run.  " . "Unable create db tables";
			return false;
		}
		return true;
	}
	function firstRun(){
		$sql = 'SHOW TABLES LIKE \'' . STOCK_TABLE . '\'';
		$res = db_query ( $sql );
		return (db_num_rows ( $res ) == 0);
	}

}