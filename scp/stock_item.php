<?php

require('staff.inc.php');
require_once(stock_INCLUDE_DIR.'class.stock.php');

$stock=$category=null;
if($_REQUEST['id'] && !($stock=stock::lookup($_REQUEST['id'])))
   $errors['err']='Unknown or invalid stock';

if($_REQUEST['cid'] && !$stock && !($category=stock_Category::lookup($_REQUEST['cid'])))
    $errors['err']='Unknown or invalid stock category';

if($_POST):
    $errors=array();
    switch(strtolower($_POST['do'])) {
        case 'create':
        case 'add':
            if(($stock=stock::add($_POST,$errors)))
                $msg='stock added successfully';
            elseif(!$errors['err'])
                $errors['err'] = 'Unable to add stock. Try again!';
        break;
        case 'update':
        case 'edit';
            if(!$stock)
                $errors['err'] = 'Invalid or unknown stock';
            elseif($stock->update($_POST,$errors)) {
                $msg='stock updated successfully';
                $_REQUEST['a']=null; //Go back to view
                $stock->reload();
            } elseif(!$errors['err'])
                $errors['err'] = 'Unable to update stock. Try again!';     
            break;
        case 'manage-stock':
            if(!$stock) {
                $errors['err']='Unknown or invalid stock';
            } else {
                switch(strtolower($_POST['a'])) {
                    case 'edit':
                        $_GET['a']='edit';
                        break;
                    case 'publish';
                        if($stock->publish())
                            $msg='stock published successfully';
                        else
                            $errors['err']='Unable to publish the stock. Try editing it.';
                        break;
                        
                    case 'retire';
                        if($stock->retire())
                            $msg='stock retired successfully';
                        else
                            $errors['err']='Unable to retire the stock!';
                        break;
                        
                    case 'activate';
                        if($stock->activate())
                            $msg='stock activated successfully';
                        else
                            $errors['err']='Unable to activate the stock!';
                        break;
                        
                    case 'unpublish';
                        if($stock->unpublish())
                            $msg='stock unpublished successfully';
                        else
                            $errors['err']='Unable to unpublish the stock. Try editing it.';
                        break;
                    default:
                        $errors['err']='Invalid action';
                }
            }
            break;
        default:
            $errors['err']='Unknown action';
    
    }
endif;


$inc='stock_categories.inc.php';
if($stock) {
    $inc='stock_view.inc.php';
    if($_REQUEST['a']=='edit')
        $inc='stock.inc.php';
}elseif($_REQUEST['a']=='add') {
    $inc='stock.inc.php';
} elseif($category && $_REQUEST['a']!='search') {
    $inc='stock_categories.inc.php';
}
$nav->setTabActive('stock');
require_once(STAFFINC_DIR.'header.inc.php');
require_once(stock_STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
?>
