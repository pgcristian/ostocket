<?php

if(!defined('OSTCLIENTINC') || !$category || !$category->getIspublic()) die('Access Denied');
?>
<h1><strong><?php echo $category->getName() ?></strong></h1>
<p>
<?php echo \Format::safe_html($category->getDescription()); ?>
</p>
<hr>
<?php
$sql='SELECT stock.stock_id as stock_id, stock.asset_id as stock, 
    status.name as Status, status.image as Image'
    .' FROM '.stock_TABLE.' stock '
    .' LEFT JOIN '.stock_STATUS_TABLE.' status ON(status.status_id=stock.status_id) '
    .' WHERE stock.ispublished=1 AND stock.category_id='.db_input($category->getId())
    .' GROUP BY stock.stock_id';
if(($res=db_query($sql)) && db_num_rows($res)) {
    echo '
         <h2>Equpment</h2>
         <div id="stock">
            <ol>';
    while($row=db_fetch_array($res)) {
        echo sprintf('
            <li>%s <a href="stock.php?id=%d" >%s &nbsp;%s</a></li>',
            '<img src="images/'.$row['Image'].'" width="20" height="20"/>',    
             $row['stock_id'],
             \Format::htmlchars($row['stock']), $row['Status']);
    }
    echo '  </ol>
         </div>
         <p><a class="back" href="index.php">&laquo; Go Back</a></p>';
}else {
    echo '<strong>Category does not have any stock. <a href="index.php">Back To Index</a></strong>';
}
?>
