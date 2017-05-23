<?php

if(!defined('OSTCLIENTINC') || !$status ) die('Access Denied');
?>
<h1><strong><?php echo $status->getName() ?></strong></h1>
<p>
<?php echo \Format::safe_html($status->getDescription()); ?>
</p>
<hr>
<?php
$sql='SELECT stock.stock_id as stock_id, stock.asset_id as stock, 
    status.name as Status, status.color as color'
    .' FROM '.stock_TABLE.' stock '
    .' LEFT JOIN '.stock_STATUS_TABLE.' status ON(status.status_id=stock.status_id) '
    .' WHERE stock.ispublished=1 AND stock.status_id='.db_input($status->getId())
    .' GROUP BY stock.stock_id';
if(($res=db_query($sql)) && db_num_rows($res)) {
    echo '
         <h2>Equpment</h2>
         <div id="stock">
            <ol>';
    while($row=db_fetch_array($res)) {
        echo sprintf('
            <li> <a href="stock.php?id=%d" %s>%s &nbsp;%s</a></li>',   
             $row['stock_id'],
             'style="color:'.$row['color'].'"',
             \Format::htmlchars($row['stock']), $row['Status']);
    }
    echo '  </ol>
         </div>
         <p><a class="back" href="index.php">&laquo; Go Back</a></p>';
}else {
    echo '<strong>Status does not have any stock. <a href="index.php">Back To Index</a></strong>';
}
?>
