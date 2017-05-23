<?php

require('staff.inc.php');
include_once(stock_INCLUDE_DIR.'class.stock_category.php');

if(!$thisstaff) {
    header('Location: stock.php');
    exit;
}

$category=null;
$tickets_status=null;
if($_REQUEST['id'] && !($category=stock_Category::lookup($_REQUEST['id'])))
    $errors['err']='Unknown or invalid category ID.';

$tickets_status=$_REQUEST['tickets'];

if($_POST){
    switch(strtolower($_POST['do'])) {
        case 'update':
            if(!$category) {
                $errors['err']='Unknown or invalid category.';
            } elseif($category->update($_POST,$errors)) {
                $msg='Category updated successfully';
            } elseif(!$errors['err']) {
                $errors['err']='Error updating category. Try again!';
            }
            break;
        case 'create':
            if(($id=stock_Category::create($_POST,$errors))) {
                $msg='stock_Category added successfully';
                $_REQUEST['a']=null;
            } elseif(!$errors['err']) {
                $errors['err']='Unable to add category. Correct error(s) below and try again.';
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']='You must select at least one category';
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $sql='UPDATE '.stock_CATEGORY_TABLE.' SET ispublic=1 '
                            .' WHERE category_id IN ('.implode(',', db_input($_POST['ids'])).')';
                    
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = 'Selected categories made PUBLIC';
                            else
                                $warn = "$num of $count selected categories made PUBLIC";
                        } else {
                            $errors['err'] = 'Unable to enable selected categories public.';
                        }
                        break;
                    case 'make_private':
                        $sql='UPDATE '.stock_CATEGORY_TABLE.' SET ispublic=0 '
                            .' WHERE category_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = 'Selected categories made PRIVATE';
                            else
                                $warn = "$num of $count selected categories made PRIVATE";
                        } else {
                            $errors['err'] = 'Unable to disable selected categories PRIVATE';
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($c=stock_Category::lookup($v)) && $c->delete())
                                $i++;
                        }

                        if($i==$count)
                            $msg = 'Selected stock Category deleted successfully';
                        elseif($i>0)
                            $warn = "$i of $count selected categories deleted";
                        elseif(!$errors['err'])
                            $errors['err'] = 'Unable to delete selected stock Category';
                        break;
                    default:
                        $errors['err']='Unknown action/command';
                }
            }
            break;
        default:
            $errors['err']='Unknown action';
            break;
    }
}

$page='stock_categories.inc.php';
if(isset($category) && isset($tickets_status))
{
    $page='stock_category_tickets.inc.php';
}
else if($category || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
{
    $page='stock_category.inc.php';
}
 
$nav->setTabActive('stock');
require(STAFFINC_DIR.'header.inc.php');
require(stock_STAFFINC_DIR.$page);
require(STAFFINC_DIR.'footer.inc.php');
?>
