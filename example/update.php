<?php

require '../SimpleTextDB.php';

$db = new SimpleTextDB('data.txt');

$content = <<<EOD
华为海外Mate 9官宣界面，重新标注UF此前华为将日本官网中介绍Mate 9性能部分的介绍做了调整。准确的说，其将“新しいUFS 2.1フラッシュメモリにより、eMMC 5.1と比較し、データ転送速度が100％高速化。”这一句话，给直接删除了。由此引发的，是华为Mate 9是否与华为P10系列一样，存在将UFS与eMMC储存颗粒混用的情况出现，并引起日本消费者的疑问乃至质疑。S相关表述

在经过几天时间后，华为将这句被删除的话给重新标注在日本官网上，并加上特殊说明。其表示这个100%的提升，数据来源于华为实验室将Mate 8与Mate 9的对比得出。看来华为删除说明的举动，其实是为了更为严谨啊。
EOD;


$row = $db->where(array('id'=>248))->update(array('username'=>'华为海外Mate 9官宣界面 重新标注UFS相关表述','password' => $content), null, 1);

var_dump($row);

