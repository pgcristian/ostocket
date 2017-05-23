<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');

?>
<h1>stock Status</h1>
<form action="index.php" method="get" id="stock-search">
    <input type="hidden" name="a" value="search">
    <div>
        <input id="query" type="text" size="20" name="q" value="<?php echo Format::htmlchars($_REQUEST['q']); ?>">
        <select name="cid" id="cid">
            <option value="">&mdash; All Categories &mdash;</option>
            <?php
            $sql='SELECT cat.category_id, cat.name, count(stock.category_id) as stock '
                .' FROM '.stock_CATEGORY_TABLE.' cat '
                .' LEFT JOIN '.stock_TABLE.' stock USING(category_id) '
                .' WHERE cat.ispublic=1 AND stock.ispublished=1 '
                .' GROUP BY cat.category_id '
                .' HAVING stock>0 '
                .' ORDER BY cat.name DESC ';
            if(($res=db_query($sql)) && db_num_rows($res)) {
                while($row=db_fetch_array($res))
                    echo sprintf('<option value="%d" %s>%s (%d)</option>',
                            $row['category_id'],
                            ($_REQUEST['cid'] && $row['category_id']==$_REQUEST['cid']?'selected="selected"':''),
                            $row['name'],
                            $row['stock']);
            }
            ?>
        </select>
        <input id="searchSubmit" type="submit" value="Search">
    </div>
    <div>
        <select name="status_id" id="status_id">
            <option value="">&mdash; Any Status &mdash;</option>
            <?php
            $sql='SELECT ht.status_id as statusID, ht.name as statusName, count(stock.status_id) as stock '
                .' FROM '.stock_STATUS_TABLE.' ht '
                .' LEFT JOIN '.stock_TABLE.' stock ON(stock.status_id=ht.status_id) '
                .' GROUP BY ht.status_id '
                .' HAVING stock>0 '
                .' ORDER BY ht.status_id ';
            if(($res=db_query($sql)) && db_num_rows($res)) {
                while($row=db_fetch_array($res))
                    echo sprintf('<option value="%d" >%s (%d)</option>',
                            $row['statusID'],
                            $row['statusName'],
                             $row['stock']);
            }
            ?>
        </select>
    </div>
</form>
<hr>
<div>
<?php
if($_REQUEST['q'] || $_REQUEST['cid'] || $_REQUEST['status_id']) { //Search.
    $sql='SELECT stock.stock_id, stock.name '
        .' FROM '.stock_TABLE.' stock '
        .' LEFT JOIN '.stock_CATEGORY_TABLE.' cat ON(cat.category_id=stock.category_id) '
        .' LEFT JOIN '.stock_STATUS_TABLE.' ft ON(ft.status_id=stock.status_id) '
        .' WHERE stock.ispublished=1 AND cat.ispublic=1';
    
    if($_REQUEST['cid'])
        $sql.=' AND stock.category_id='.db_input($_REQUEST['cid']);
    
    if($_REQUEST['status_id'])
        $sql.=' AND ft.status_id='.db_input($_REQUEST['status_id']);


    if($_REQUEST['q']) {
        $sql.=" AND stock.name LIKE ('%".db_input($_REQUEST['q'],false)."%') 
                 OR stock.serialnumber LIKE ('%".db_input($_REQUEST['q'],false)."%') 
                 OR stock.description LIKE ('%".db_input($_REQUEST['q'],false)."%')";
    }

    $sql.=' GROUP BY stock.stock_id';

    echo "<div><strong>Search Results</strong></div><div class='clear'></div>";
    if(($res=db_query($sql)) && ($num=db_num_rows($res))) {
        echo '<div id="stock">'.$num.' stock matched your search criteria.
                <ol>';
        while($row=db_fetch_array($res)) {
            echo sprintf('
                <li><a href="stock.php?id=%d" class="previewstock">%s</a></li>',
                $row['stock_id'],$row['name'],$row['ispublished']?'Published':'Internal');
        }
        echo '  </ol>
             </div>';
    } else {
        echo '<strong class="faded">The search did not match any items.</strong>';
    }
} else { //Category Listing.
    $sql='SELECT cat.category_id, cat.name, cat.description, cat.ispublic, count(stock.stock_id) as stock '
        .' FROM '.stock_CATEGORY_TABLE.' cat '
        .' LEFT JOIN '.stock_TABLE.' stock ON(stock.category_id=cat.category_id AND stock.ispublished=1) '
        .' WHERE cat.ispublic=1 '
        .' GROUP BY cat.category_id '
        .' HAVING stock>0 '
        .' ORDER BY cat.name';
    if(($res=db_query($sql)) && db_num_rows($res)) {
        echo '<div>Click on the category to browse stock.</div>
                <ul id="stock">';
        while($row=db_fetch_array($res)) {

            echo sprintf('
                <li>
                    <i></i>
                    <h4><a href="stock.php?cid=%d">%s (%d)</a></h4>
                    %s
                </li>',$row['category_id'],
                Format::htmlchars($row['name']),$row['stock'],
                Format::safe_html($row['description']));
        }
        echo '</ul>';
        
         $sql='SELECT status.status_id, status.name, status.color as color, count(stock.stock_id) as stock '
        .' FROM '.stock_STATUS_TABLE.' status '
        .' LEFT JOIN '.stock_TABLE.' stock ON(stock.status_id=status.status_id AND stock.ispublished=1) '
        .' GROUP BY status.status_id'
        .' HAVING stock>0 '
        .' ORDER BY status.name';
    if(($res=db_query($sql)) && db_num_rows($res)) {
        echo '<div>Click on the status to browse stock.</div>
                <ul id="stock_status">';
        while($row=db_fetch_array($res)) {

            echo sprintf('
                <li>
                    <i></i>
                    <h4><a href="stock.php?status=%d" %s>%s (%d)</a></h4>
                    
                </li>',
                   
                    $row['status_id'],
                     'style="color:'.$row['color'].'"',
                Format::htmlchars($row['name']),$row['stock']);
                
        }
        echo '</ul>';
    } 
    }
    else {
        echo 'NO items found';
    }
}
?>
</div>