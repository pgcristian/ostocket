<?php

$category=null;
$eq_status=null;
if($_REQUEST['cid'] && !($category=Equipment_Category::lookup($_REQUEST['cid'])))
    $errors['err']='Unknown or invalid equipment category';
if($_REQUEST['status_id'] && !($eq_status=Equipment_Status::lookup($_REQUEST['status_id'])))
    $errors['err']='Unknown or invalid equipment status';

$inc='equipment_lists.inc.php';
if($category && $_REQUEST['a']!='search') {
    $inc='equipment_list.inc.php';
}
else if($eq_status)
{
    $inc='equipment_status_list.inc.php';
}
$nav->setTabActive('equipment');
require_once(STAFFINC_DIR.'header.inc.php');
require_once(EQUIPMENT_STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
?>