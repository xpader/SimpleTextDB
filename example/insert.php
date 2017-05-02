<?php

require '../SimpleTextDb.php';

$db = new SimpleTextDB('data.txt');

//$row = $db->insert(array(
//	'username' => uniqid(),
//	'password' => md5(time()),
//	'login_ip' => rand(10000000, 99999999)
//));

//$row = $db->insertBatch(array(
//	array(
//		'username' => uniqid(),
//		'password' => md5(time()),
//		'login_ip' => rand(10000000, 99999999)
//	),
//	array(
//		'username' => uniqid(),
//		'password' => md5(time()),
//		'login_ip' => rand(10000000, 99999999)
//	)
//));

var_dump($row);

