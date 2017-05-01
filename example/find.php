<?php

require '../SimpleTextDb.php';

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

$row = $db->where(array('id >='=>111, 'id <='=>250))->order(SORT_DESC)->limit(10)->findAll();

//$row = $db->findAll();

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

