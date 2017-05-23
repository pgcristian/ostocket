<?php
if(!defined('OSTCLIENTINC') || !$stock  || !$stock->getIspublished()) die('Access Denied');

$category=$stock->getCategory();

?>
<h1>stock</h1>
<div id="breadcrumbs">
    <a href="index.php">All Categories</a>
    &raquo; <a href="stock.php?cid=<?php echo $category->getId(); ?>"><?php echo $category->getName(); ?></a>
</div>
<div style="width:700;padding-top:2px; float:left;">
<strong style="font-size:16px;"><?php echo $stock->getAsset_id() ?></strong>
</div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;"></div>
<div class="clear"></div>
<p>
<img src="<?php echo "images/".$stock->getStatus()->getImage();?>" width="20" height="20"/>
<?php echo Format::safe_html($stock->getStatus()); ?>

</p>
<hr>
<div class="faded">&nbsp;Last updated <?php echo Format::db_daydatetime($category->getUpdated()); ?></div>