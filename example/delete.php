<?php

require '../SimpleTextDB.php';

$db = new SimpleTextDB('data.txt');

$row = $db->where(array('id'=>112))->delete();

var_dump($row);

