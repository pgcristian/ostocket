<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__).'/include');
return array(
    'id' =>             'ostocket:stock',
	'version' =>        '0.1',
    'name' =>           'Stock controller in tickets',
    'author' =>         'pgcristian',
    'description' =>    'Possibility to link stocks to tickets',
	'plugin' =>         'stock.php:StockPlugin'
);

?>