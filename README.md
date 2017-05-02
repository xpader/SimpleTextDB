# SimpleTextDb
A Simple Text Database\
一个简单的文本数据库

Base on text file, implemented simple insert,delete,update,fetch functions to help you build small programs.

本程序建议仅用于低频访问且较简单的程序。\
应用场景及设计上限制不能对并发写，原子安全，和更多功能提供保障，请勿用于高并发和密集写场景中。

## 示例

##### 创建数据库
创建指定名称的数据库文件
```php
$result = SimpleTextDB::createTable('data.txt', ['id', 'username', 'password', 'login_ip' ,'description'], 'id');
```
第三个参数指定 id 字段为自增片段，如果没有则不指定。

##### 插入数据
```php
$db = new SimpleTextDB('data.txt');

$row = $db->insert(array(
	'username' => uniqid(),
	'password' => md5(time()),
	'login_ip' => rand(10000000, 99999999)
));
```
成功返回 true 或自增 ID，失败返回 false

##### 修改数据
```php
$row = $db->where(array('id'=>248))->update(array('username'=>'Hello','password' => 'NewPassword'), null, 1);
```
修改 id=248 记录的内容

##### 删除数据
```php
$db = new SimpleTextDB('data.txt');

$row = $db->where(array('id'=>112))->delete();
```

##### 查询数据
- where() 指定查询的条件，其中键为字段，值为对应的查询值，默认为等于查询 。
- 如果使用其它查询，指定数组元素为 ['id >'=>123] 则代表大于查询
- where() 第二个参数代表条件是否使用 OR （或）组成
- order() 只能指定整个数据插入的先后顺序，暂不能指定字段排序
- findOne() 和 findAll() 等方法允许传递一个 callable $filter 的函数来过滤数据

```php
$row = $db->findOne(function($row) {
	return $row['username'] == 'test';
});

$rows = $db->where(array('id >='=>121, 'id <='=>145))->order(SORT_DESC)->limit(2, 20)->findAll();
```

$where 条件中的字段名可以指定的查询对比符号列表
- = 等于，不写时默认为等于，如果传入的元素值为数组，则代表 IN 查询
- != 不等于
- \> 大于
- \>= 大于等于
- \< 小于
- \<= 小于等于
- % 模糊匹配
- !% 模糊非匹配
 
###### 更多功能和用法详见 SimpleTextDB.php 示例及源码。
