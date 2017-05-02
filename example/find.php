<?php

require '../SimpleTextDB.php';

$db = new SimpleTextDB('data.txt');

//$row = $db->insert(array(
//	'username' => uniqid(),
//	'password' => md5(time()),
//	'login_ip' => rand(10000000, 99999999)
//));

//$row = $db->where(array('id'=>142))->findOne();

//$row = $db->findAll(function($row) {
//	return $row['username'] == 'Pader';
//});

$row = $db->where(array('id >='=>121, 'id <='=>145))->order(SORT_DESC)->limit(2, 20)->findAll();

//$row = $db->where(array('id >='=>121, 'id <='=>145))->count();

//$row = $db->order(SORT_DESC)->limit(10)->findAll();

//$row = $db->update(array(
//	'username' => 'test1'
//), function($row) {
//	return $row['id'] == '102';
//}, 1);
//
//$row = $db->delete(function($row) {
//	return $row['id'] == 101;
//});

//$row = SimpleTextDb::createTable('demo.dat', ['id', 'key', 'value', 'last_update'], 'id');

print_r($row);

