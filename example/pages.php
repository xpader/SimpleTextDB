<?php
/**
 * SimpleTextDB 分页演示
 * 配合VgotFaster框架的分页类的演示
 */
require '../SimpleTextDB.php';
require 'Pagination.php';

//初始化数据库
$db = new SimpleTextDB('data.txt');

//查询条件，根据实际情况来..
//此条件在获取总数和分页数据时需要用到
$where = array('id >'=>120);

$total = $db->where($where)->count(); //统计出总数
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; //获取当前页数
$num = 10; //每页要显示的数量

//初始化分页类
$pagination = new Pagination(array(
	'totalRows' => $total,
	'curPage' => $page,
	'pageUrl' => '?page=*',
	'perPage' => $num
));

//获取到开始取数据的位置和分页数据
$offset = $pagination->getStart();
$data = $db->where($where)->order(SORT_DESC)->limit($offset, $num)->findAll();

$pageHtml = $pagination->makeLinks(); //生成分页的HTML链接
$pageCount = $pagination->getPageCount(); //总页数

//页面演示
?>
<style type="text/css">
.fpage {font-size:14px; padding:3px; margin:3px; text-align:center;}
.fpage a,.fpage b {background-color:#FFF; padding:5px 7px; margin:0 2px; color:#333; border:1px solid #C2D5E3; display:inline-block; vertical-align:middle; text-decoration:none;}
.fpage a:hover {border-color:#336699; text-decoration:none;}
.fpage b {background-color:#E5EDF2;}
.fpage input {border:#1586D6 1px solid; color:#036CB4; vertical-align:middle; text-align:center;}
</style>
<table align="center" border="1" cellspacing="0" cellpadding="5">
	<tr>
		<th>id</th>
		<th>username</th>
		<th>password</th>
		<th>login_ip</th>
	</tr>
	<?php foreach ($data as $row) { ?>
		<tr>
			<td><?=$row['id']?></td>
			<td><?=$row['username']?></td>
			<td><?=$row['password']?></td>
			<td><?=$row['login_ip']?></td>
		</tr>
	<?php } ?>
</table>
<pre>
</pre>
<p style="text-align:center;">总共 <?=$pageCount?> 页</p>
<?=$pageHtml?>

