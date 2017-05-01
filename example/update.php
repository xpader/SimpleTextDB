<?php

require '../SimpleTextDb.php';

$db = new SimpleTextDB('data.txt');

$row = $db->where(array('id'=>142))->update(array(
	'password' => '伴随着创作者更新（包括移动和PC）的正式发布，微软已经将更多的精力集中到Windows 10下个里程碑--RedStone 3的开发上。在最新发布的Build 16184版本中，带来了诸多Windows粉丝渴望看到的新功能。

尽管开机初步体验并没有发生明显的改变，但当你深入探索之后就会注意到微软已经在不同的应用上测试不同的风格，类似于Groove或者Paint 3D的应用的应用程序已经支持“Project Neon”，可以看到这个磨砂玻璃，不同的应用会存在差异。'
), null, 1);

var_dump($row);

