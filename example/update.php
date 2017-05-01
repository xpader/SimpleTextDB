<?php

require '../SimpleTextDb.php';

$db = new SimpleTextDB('data.txt');

$row = $db->update(array(
	'username' => 'test1'
), function($row) {
	return $row['id'] == '102';
}, 1);

var_dump($row);

