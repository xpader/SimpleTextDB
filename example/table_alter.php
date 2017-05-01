<?php

require '../SimpleTextDb.php';

SimpleTextDB::createTable('table2.txt', ['id', 'username', 'password', 'login_ip' ,'description'], 'id');

$db2 = new SimpleTextDB('table2.txt');
$db = new SimpleTextDB('data.txt');

foreach ($db->findAll() as $row) {
	$db2->insert($row);
}


