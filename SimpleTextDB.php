<?php

/**
 * A Simple Text Database
 *
 * Base on text file, implemented simple insert,delete,update,fetch functions to help you build small programs.
 * 本程序建议仅用于低频访问且较简单的程序。
 * 应用场景及设计上限制不能对并发写，原子安全，和更多功能提供保障，请勿用于高并发和密集写场景中。
 *
 * @author Pader <ypnow#163.com>
 * @link https://github.com/xpader
 */
class SimpleTextDB
{

	protected $file;
	protected $delimiter = "\x1F"; //记录分割符

	protected $_where = array();
	protected $_limit;
	protected $_offset = 0;
	protected $_order;

	public function __construct($file)
	{
		if (!is_file($file)) {
			trigger_error("指定的数据文件不存在：$file", E_USER_ERROR);
		}

		$this->file = $file;
	}

	/**
	 * 查找一行
	 *
	 * @param callable $filter
	 * @return array|null
	 */
	public function findOne(callable $filter=null)
	{
		$table = $this->readTableWithData();
		$result = null;
		$noFilter = ($filter === null && !$this->_where);

		foreach ($table['data'] as $row) {
			$row = $this->parseRow($row, $table['keys']);

			if ($noFilter || $this->filter($row, $filter)) {
				$result = $row;
				break;
			}
		}

		$this->clearCondition();

		return $result;
	}

	/**
	 * 读取多行
	 *
	 * @param callable $filter
	 * @return array
	 */
	public function findAll(callable $filter=null)
	{
		$table = $this->readTableWithData();
		$result = array();
		$match = $add = 0;
		$hasFilter = ($filter !== null || $this->_where);

		//数据顺序排序，仅针对插入顺序
		if ($this->_order == SORT_DESC) {
			$table['data'] = array_reverse($table['data']);
		}

		//$i = $this->_order == SORT_DESC ? count($table['data']) : 0;

		foreach ($table['data'] as $row) {
		//while (true) {
		//	if ($this->_order == SORT_DESC) {
		//		--$i;
		//	} else {
		//		++$i;
		//	}
		//
		//	if (!isset($table['data'][$i])) {
		//		break;
		//	}
		//
		//	$row = $table['data'][$i];

			if ($hasFilter) {
				$row = $this->parseRow($row, $table['keys']);
				if ($this->filter($row, $filter)) {
					++$match;
				} else {
					continue;
				}
			} else {
				++$match;
			}

			//OFFSET
			if ($this->_offset > 0 && $match <= $this->_offset) {
				continue;
			}

			//LIMIT
			$result[] = $hasFilter ? $row : $this->parseRow($row, $table['keys']);
			++$add;

			if ($this->_limit > 0 && $add == $this->_limit) {
				break;
			}
		}

		$this->clearCondition();

		return $result;
	}

	/**
	 * 统计指定数据记录数
	 *
	 * @param callable|null $filter
	 * @return int
	 */
	public function count(callable $filter=null)
	{
		$table = $this->readTableWithData();
		$hasFilter = ($filter !== null || $this->_where);

		if (!$hasFilter) return count($table['data']);

		$count = 0;

		foreach ($table['data'] as $row) {
			$row = $this->parseRow($row, $table['keys']);
			if ($this->filter($row, $filter)) {
				++$count;
			}
		}

		$this->clearCondition();

		return $count;
	}

	/**
	 * 对数据进行条件过滤
	 *
	 * @param array $row
	 * @param callable|null $callback
	 * @return bool
	 * @throws STDException
	 */
	protected function filter($row, callable $callback=null)
	{
		if ($this->_where) {
			list($or, $condition) = $this->_where;
			$match = false;

			foreach ($condition as $k => $v) {
				if (strpos($k, ' ')) {
					list($k, $symbol) = explode(' ', $k);
				} else {
					$symbol = '=';
				}

				switch ($symbol) {
					case '=': //等于
					case '!=': //不等于
						$is = is_array($v) ? in_array($row[$k], $v) : strcasecmp($v, $row[$k]) == 0;
						$symbol == '!=' && $is = !$is;
						break;
					case '>': $is = $row[$k] > $v; break; //大于
					case '>=': $is = $row[$k] >= $v; break;
					case '<': $is = $row[$k] < $v; break; //小于
					case '<=': $is = $row[$k] <= $v; break;
					case '%': //模糊匹配
					case '!%': //模糊非匹配
						$is = stripos($row[$k], $v) !== false;
						$symbol == '!%' && $is = !$is;
						break;
					default:
						throw new STDException("Unknow condition char '$symbol'.");
				}

				if (!$or) {
					//AND 条件下任一项不匹配，视为匹配失败
					if (!$is) {
						break;
					}
				} elseif ($is) {
					//OR 条件下，任何一项匹配，视为匹配成功
					$match = true;
					break;
				}
			}

			if (!$or && $is) {
				$match = true;
			}
		} else {
			$match = true;
		}

		if ($match && $callback) {
			return $callback($row);
		} else {
			return $match;
		}
	}

	public function where($condition, $or=false)
	{
		$this->_where = [$or, $condition];
		return $this;
	}

	public function limit($offset, $num=null)
	{
		if ($num === null) {
			$this->_limit = $offset;
		} else {
			$this->_limit = $num;
			$this->_offset = $offset;
		}

		return $this;
	}

	public function offset($offset)
	{
		$this->_offset = $offset;
		return $this;
	}

	public function order($order=SORT_ASC)
	{
		$this->_order = $order;
		return $this;
	}

	protected function clearCondition()
	{
		$this->_where = array();
		$this->_limit = $this->_order = null;
		$this->_offset = 0;
	}

	/**
	 * 插入数据
	 *
	 * @param array $data
	 * @return bool
	 */
	public function insert($data)
	{
		$table = $this->readTableWithData();

		//AutoId
		if ($table['autoKey']) {
			if (isset($data[$table['autoKey']])) {
				if ($data[$table['autoKey']] >= $table['autoId']) {
					$table['autoId'] = $data[$table['autoKey']] + 1;
				}
			} else {
				$data[$table['autoKey']] = $table['autoId'];
				++$table['autoId'];
			}
		}

		$row = $this->joinRow($data, $table['keys']);
		$table['data'][] = $row;

		return $this->writeTable($table) ? ($table['autoKey'] ? $data[$table['autoKey']] : true) : false;
	}

	/**
	 * 批量插入数据
	 *
	 * @param array $batchData
	 * @return bool|int
	 */
	public function insertBatch($batchData)
	{
		$table = $this->readTableWithData();

		foreach ($batchData as $data) {
			//AutoId
			if (isset($data[$table['autoKey']])) {
				if ($data[$table['autoKey']] >= $table['autoId']) {
					$table['autoId'] = $data[$table['autoKey']] + 1;
				}
			} else {
				$data[$table['autoKey']] = $table['autoId'];
				++$table['autoId'];
			}

			$row = $this->joinRow($data, $table['keys']);

			$table['data'][] = $row;
		}

		return $this->writeTable($table) ? count($batchData) : false;
	}

	/**
	 * 更新数据
	 *
	 * @param array $data
	 * @param callable|null $filter
	 * @param int $limit
	 * @return int
	 */
	public function update($data, callable $filter=null, $limit=0)
	{
		$table = $this->readTableWithData();
		$count = 0;
		$hasFilter = ($filter !== null || $this->_where);

		foreach ($table['data'] as $i => $row) {
			$row = $this->parseRow($row, $table['keys']);
			if ($hasFilter) {
				if ($this->filter($row, $filter)) {
					$table['data'][$i] = $this->joinRow($data + $row, $table['keys']);
					++$count;
				}
			} else {
				$table['data'][$i] = $this->joinRow($data + $row, $table['keys']);
				++$count;
			}

			if ($limit > 0 && $count == $limit) {
				break;
			}
		}

		$this->clearCondition();

		if ($count > 0) {
			$this->writeTable($table);
		}

		return $count;
	}

	/**
	 * 删除数据
	 *
	 * @param callable|null $filter
	 * @param int $limit
	 * @return int
	 */
	public function delete(callable $filter=null, $limit=0)
	{
		$table = $this->readTableWithData();
		$count = 0;
		$hasFilter = ($filter !== null || $this->_where);

		//清空表数据
		if (!$hasFilter) {
			$count = count($table['data']);
			$table['data'] = [];
		} else {
			foreach ($table['data'] as $i => $row) {
				if ($hasFilter) {
					$row = $this->parseRow($row, $table['keys']);
					if ($this->filter($row, $filter)) {
						unset($table['data'][$i]);
						++$count;
					}
				} else {
					unset($table['data'][$i]);
					++$count;
				}

				if ($limit > 0 && $count == $limit) {
					break;
				}
			}
		}

		if ($count > 0) {
			$this->writeTable($table);
		}

		return $count;
	}

	/**
	 * 读取表结构
	 *
	 * @return array
	 */
	public function readTable()
	{
		//读取表头
		$fp = fopen($this->file, 'r');
		$head = fgets($fp, 512);
		fclose($fp);

		$head = trim($head);
		return static::parseHead($head);
	}

	/**
	 * 读取表结构和数据
	 *
	 * @return array
	 */
	protected function readTableWithData()
	{
		$file = file($this->file, FILE_IGNORE_NEW_LINES);

		$head = trim($file[0]);
		$table = static::parseHead($head);
		$table['data'] = array_slice($file, 1);

		return $table;
	}

	/**
	 * 写入数据文件
	 *
	 * @param array $table
	 * @return bool|int
	 */
	protected function writeTable($table)
	{
		$head = static::makeHead($table['keys'], $table['autoKey'], $table['autoId']);
		$file = array_merge([$head], $table['data']);
		return file_put_contents($this->file, join("\r\n", $file), LOCK_EX);
	}

	/**
	 * 解析表头内容
	 *
	 * @param string $head
	 * @return array
	 */
	protected static function parseHead($head)
	{
		list($name, $version, $keys, $autoKey, $autoId) = explode('|', $head);
		$keys = explode(',', $keys);

		return compact('version', 'keys', 'autoKey', 'autoId');
	}

	protected static function makeHead($keys, $autoKey='', $autoId=0)
	{
		return 'SimpleTextDB|1|'.join(',', $keys).'|'.$autoKey.'|'.$autoId;
	}

	/**
	 * 组成表数据行
	 *
	 * 将输入的行数组键值对转换成存储的字符串行
	 *
	 * @param array $data
	 * @param array $keys
	 * @return string
	 * @throws
	 */
	protected function joinRow($data, $keys)
	{
		$row = [];

		foreach ($keys as $key) {
			if (isset($data[$key])) {
				$row[] = $data[$key];
				unset($data[$key]);
			} else {
				$row[] = '';
			}
		}

		if ($data) {
			$errorKey = key($data);
			throw new STDException("Unknow column name '$errorKey'.");
		}

		$row = join($this->delimiter, $row);
		return str_replace(array("\r", "\n"), array('\\\\r', '\\\\n'), $row);
	}

	/**
	 * 解析行数据
	 *
	 * 将存储的字符集串行解析成输出的数组键值对
	 *
	 * @param string $row
	 * @param array $keys
	 * @return array
	 */
	protected function parseRow($row, $keys)
	{
		$row = str_replace(array('\\\\r', '\\\\n'), array("\r", "\n"), $row);
		$row = explode($this->delimiter, $row);
		return array_combine($keys, $row);
	}

	/**
	 * 创建一个表文件
	 *
	 * @param string $filename 表文件名
	 * @param array $keys 表字段列表
	 * @param string $autoKey 表自动ID字段
	 * @param int $autoId 表自动ID起始数字
	 * @return bool|int
	 */
	public static function createTable($filename, $keys, $autoKey='', $autoId=1)
	{
		$head = static::makeHead($keys, $autoKey, ($autoId) ? $autoId : 0);
		return file_put_contents($filename, $head."\r\n", LOCK_EX);
	}

}

class STDException extends Exception {

}