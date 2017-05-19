<?php
require_once('../client.inc.php');
if(!\model\Equipment::countPublishedEquipment()) {
    header('Location: ../');
    exit;
}
require_once(CLIENTINC_DIR.'header.inc.php');
$dashboard = new \controller\Dashboard();
if(isset($dashboard))
{
    $dashboard->viewClientPage();
}
require_once(CLIENTINC_DIR.'footer.inc.php');
?>