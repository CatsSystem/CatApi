<?php
/**
 * MySQL语法生成模块
 *
 * @package bilibili
 * @author MagicBear<magicbearmo@gmail.com>
 * @version 1.0
 */

namespace base\model;

use base\async\db\AsyncModel;
use base\promise\Promise;
use base\sync\db\BaseDB;

class MySQLStatement extends DbQuery
{
	private $_is_executed = false;
	private $_sql = array(
		"class"=>"",
		"base"=>"",
		"base_options"=>"",
		"fields"=>"",
		"base_table"=>"",
		"join"=>array(),
		"where"=>"",
		"groupby"=>array(),
		"having"=>array(),
		"order"=>array(),
		"limit"=>array()
		);

    public static function  prepare()
    {
        return new MySQLStatement();
    }

	private function __construct() {

	}

	private function getTableSQL($tbl)
	{
		$tbl_name = "";
		if (strpos($tbl,".")!==false)
		{
			$db = explode(".", $tbl);
			$tbl_name.="`{$db[0]}`.";
			$tbl = $db[1];
		}
		if (strpos($tbl," ")!==false)
		{
			$db = explode(" ", $tbl);
			$tbl_name.="`".$db[0]."` ".$db[1];
		}else
		{
			$tbl_name.="`".$tbl."`";
		}
		return $tbl_name;
	}

	private function getFieldSQL($tbl)
	{
		$tbl_name = "";
		if (strpos($tbl,".")!==false)
		{
			$db = explode(".", $tbl);
			$tbl_name.="`{$db[0]}`.";
			$tbl = $db[1];
		}
		if (strpos($tbl," ")!==false)
		{
			$db = explode(" ", $tbl);
			$tbl_name.="`".$db[0]."` as ".$db[1];
		}else
		{
			$tbl_name.="`".$tbl."`";
		}
		return $tbl_name;
	}

	/**
	 * 初始化SELECT语句
	 * @param string $table 表名
	 * @param string $fields 需要获取的栏位名（默认为*）
	 * @param string $options SQL语句选项（默认为空）
	 * @return MySQLStatement
	 */
	public function select($table, $fields="*", $options = "")
	{
		$this->_sql['class'] = "SELECT";
		$this->_sql['fields'] = $fields;
		$this->_sql['base_options'] = "{$options}";
		$this->_sql['base_table'] = $this->getTableSQL($table);
		return $this;
	}

	/**
	 * 初始化INSERT语句
	 * @param string $table 表名
	 * @param mixed $vars 需要插入的数据 （如果为字串则直接传入）
	 * @param string $options SQL语句选项（默认为LOW_PRIORITY）
	 * @param string $on_duplicate SQL语句选项（默认为LOW_PRIORITY）
	 * @return MySQLStatement
	 */
	public function insert($table, $vars="", $options = "LOW_PRIORITY", $on_duplicate = null)
	{
		$this->_sql['class'] = "INSERT";
		$this->_sql['fields'] = (gettype($vars)=="string" ? $vars : $this->buildQueryList($vars, ","));
		$this->_sql['base_options'] = "{$options}";
		$this->_sql['base_table'] = $this->getTableSQL($table);
		$this->_sql['on_duplicate'] = $on_duplicate;
		return $this;
	}

	/**
	 * 初始化REPLACE语句
	 * @param string $table 表名
	 * @param \mixed $vars 需要替换的数据 （如果为字串则直接传入）
	 * @param string $options SQL语句选项（默认为LOW_PRIORITY）
	 * @return MySQLStatement
	 */
	public function replace($table, $vars="", $options = "LOW_PRIORITY")
	{
		$this->_sql['class'] = "REPLACE";
		$this->_sql['fields'] = (gettype($vars)=="string" ? $vars : $this->buildQueryList($vars, ","));
		$this->_sql['base_options'] = "{$options}";
		$this->_sql['base_table'] = $this->getTableSQL($table);
		return $this;
	}

	/**
	 * 初始化UPDATE语句
	 * @param string $table 表名
	 * @param \mixed $vars 需要修改的数据 （如果为字串则直接传入）
	 * @param string $options SQL语句选项（默认为LOW_PRIORITY）
	 * @return MySQLStatement
	 */
	public function update($table, $vars="", $options = "LOW_PRIORITY")
	{
		$this->_sql['class'] = "UPDATE";
		$this->_sql['fields'] = (gettype($vars)=="string" ? $vars : $this->buildQueryList($vars, ","));
		$this->_sql['base_options'] = "{$options}";
		$this->_sql['base_table'] = $this->getTableSQL($table);
		return $this;
	}

	/**
	 * 初始化DELETE语句
	 * @param string $table 表名
	 * @param string $options SQL语句选项（默认为LOW_PRIORITY）
	 * @return MySQLStatement
	 */
	public function delete($table, $options = "LOW_PRIORITY")
	{
		$this->_sql['class'] = "DELETE";
		$this->_sql['base_options'] = "{$options}";
		$this->_sql['base_table'] = $this->getTableSQL($table);
		return $this;
	}

	/**
	 * 添加条件
	 * @param \mixed $params 条件
	 * @return MySQLStatement
	 */
	public function where($params)
	{
		$query = (gettype($params)=="string" ? $params : $this->buildQueryList($params));
		if(empty($this->_sql['where']) ) {
			$this->_sql['where'] = "";
		} else {
			$this->_sql['where'] .= ' AND ';
		}
		if ($query) $this->_sql['where'] .= $query;
		return $this;
	}

	public function orWhere($params)
	{
		$query = (gettype($params)=="string" ? $params : $this->buildQueryList($params, ' OR '));
		if(empty($this->_sql['where']) ) {
			$this->_sql['where'] = "";
		} else {
			$this->_sql['where'] .= ' OR ';
		}
		if ($query) $this->_sql['where'] .= $query;
		return $this;
	}

	/**
	 * 添加排序
	 * @param string $field 要排序的栏位
	 * @param string $direction 方向 可选
	 *  - ASC   - 正向排序
	 *  - DESC  - 逆向排序
	 * @return MySQLStatement
	 */
	public function order($field, $direction="ASC")
	{
		$this->_sql['order'][] = $this->getFieldSQL($field)." ".$direction;
		return $this;
	}

	/**
	 * 添加JOIN表
	 * @param string $table 要JOIN的表
	 * @param \mixed $params JOIN的条件
	 * @param string $direction JOIN的方向(默认LEFT) 可选
	 *  - LEFT   - 左方向JOIN 当原表中有时则JOIN的表沒有时也返回(JOIN表的数据为null)
	 *  - RIGHT  - 右方向JOIN 当JOIN的表有时 如原表中沒有也返回(原表的数据为null)
	 *  - INNER  - JOIN及原表都有数据时才返回
	 * @return MySQLStatement
	 * @throws \Exception
	 */
	public function join($table, $params, $direction = "LEFT")
	{
		if (!in_array(strtoupper($direction), array("LEFT", "RIGHT", "INNER")))
		{
			throw new \Exception("JOIN direction is only allow LEFT, RIGHT, INNER");
		}
		$query = (gettype($params)=="string" ? $params : $this->buildQueryList($params));
		$this->_sql['join'][] = " ".$direction." JOIN ".$this->getTableSQL($table).(preg_match("/^[0-9a-zA-Z_]+$/", $query) ? " USING (`{$query}`)" : " ON ".$query);
		return $this;
	}

	/**
	 * 添加统计条件
	 * @param array $params 统计的条件
	 * @return MySQLStatement
	 */
	public function groupby($params)
	{
		//$query = (gettype($params)=="string" ? $params : $this->buildQueryList($params));
		$this->_sql['groupby'][] = $params;
		return $this;
	}

	/**
	 * 添加统计后返回条件
	 * @param array $params 统计的条件
	 * @return MySQLStatement
	 */
	public function having($params)
	{
		//$query = (gettype($params)=="string" ? $params : $this->buildQueryList($params));
		$this->_sql['having'][] = $params;
		return $this;
	}

	/**
	 * 添加数据返回/更新限制
	 * @param int $start SELECT时为起始偏移 INSERT/UPDATE/DELETE时为限制数量
	 * @param int $count 仅SELECT时有效 返回数量
	 * @return MySQLStatement
	 */
	public function limit($start, $count=20)
	{
		$this->_sql['limit'] = array($start, $count);
		return $this;
	}

	/**
	 * 添加计算SELECT时命中的数据数量 (仅SELECT有效)
	 * @return MySQLStatement
	 */
	public function calcCount()
	{
		if (strpos("SQL_CALC_FOUND_ROWS", $this->_sql['base_options'])==false && $this->_sql['class']=="SELECT")
		{
			$this->_sql['base_options'] = trim($this->_sql['base_options']." SQL_CALC_FOUND_ROWS");
		}
		return $this;
	}

	/**
	 * 生成完整SQL语句
	 * @return string 生成的SQL语句
	 * @throws \Exception
	 */
	public function sql()
	{
		if (!$this->_sql['class'])
		{
			throw new \Exception('Please select/insert/update/delete first.');
		}
		$_sql = $this->_sql['base'];
		switch ($this->_sql['class'])
		{
			case "SELECT":
				$_sql = $this->_sql['class']." ".($this->_sql['base_options'] ? $this->_sql['base_options']." " : "").$this->_sql['fields']." FROM ".$this->_sql['base_table'];
				if ($this->_sql['join'])
				{
					$_sql.=implode("", $this->_sql['join']);
				}
				break;
			case "UPDATE":
				$_sql = $this->_sql['class']." ".($this->_sql['base_options'] ? $this->_sql['base_options']." " : "").$this->_sql['base_table'];
				if ($this->_sql['join'])
				{
					$_sql.=implode("", $this->_sql['join']);
				}
				$_sql.=" SET ".$this->_sql['fields'];
				break;
			case "INSERT":
				$_sql = $this->_sql['class']." ".($this->_sql['base_options'] ? $this->_sql['base_options']." " : "")." INTO ".$this->_sql['base_table']." SET ".$this->_sql['fields'];
				if ($this->_sql['on_duplicate'])
				{
					$_xtra_sql = "";
					foreach ($this->_sql['on_duplicate'] as $key => $value)
					{
						$_xtra_sql.=($_xtra_sql ? "," : "")."`{$key}`=`{$key}`+'".addslashes($value)."'";
					}
					if ($_xtra_sql)
					{
						$_sql.=" ON DUPLICATE KEY UPDATE ".$_xtra_sql;
					}
				}
				break;
			case "REPLACE":
				$_sql = $this->_sql['class']." ".($this->_sql['base_options'] ? $this->_sql['base_options']." " : "")." INTO ".$this->_sql['base_table']." SET ".$this->_sql['fields'];
				break;
			case "DELETE":
				$_sql = $this->_sql['class']." ".($this->_sql['base_options'] ? $this->_sql['base_options']." " : "")." FROM ".$this->_sql['base_table'];
				break;
		}

		if ($this->_sql['where'] && !in_array($this->_sql['class'],array("INSERT","REPLACE")))
		{
			$_sql.=" WHERE ".$this->_sql['where'];
		}
		if ($this->_sql['groupby'] && $this->_sql['class'] == "SELECT")
		{			
			$_sql.=" GROUP BY ".join(",", $this->_sql['groupby']);
			if ($this->_sql['having'])
			{
				$_sql.=" HAVING ".join(",", $this->_sql['having']);
			}
		}
		if ($this->_sql['order'] && $this->_sql['class'] == "SELECT")
		{
			$_order_sql = "";
			foreach ($this->_sql['order'] as $order_sql)
			{
				$_order_sql.=($_order_sql ? "," : "").$order_sql;
			}
			if ($_order_sql) $_sql.=" ORDER BY ".$_order_sql;
		}
		if ($this->_sql['limit'] && $this->_sql['class'] == "SELECT")
		{
			$_sql.=" LIMIT ".$this->_sql['limit'][0].",".$this->_sql['limit'][1];
		}elseif ($this->_sql['limit'])
		{
			$_sql.=" LIMIT ".$this->_sql['limit'][0];
		}

		return $_sql;
	}

	/**
	 * 执行SQL语句
	 * @param Promise $promise
	 * @throws \Exception
	 */
	public function query(Promise $promise)
	{
		$this->_is_executed = true;
		$model = new AsyncModel("");
		$model->query([$this->sql(), false], $promise);
	}

	public function getOne(Promise $promise)
	{
		$this->_is_executed = true;
		$model = new AsyncModel("");
		$model->query([$this->sql(), true], $promise);
	}

	public function sync_query() {
		$this->_is_executed = true;
		$statement = BaseDB::getInstance()->query($this->sql());
		if( empty($statement) ) {
			return null;
		}
		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function sync_getOne()
	{
		$this->_is_executed = true;
		$statement = BaseDB::getInstance()->query($this->sql());
		if( empty($statement) ) {
			return null;
		}
		return $statement->fetch(\PDO::FETCH_ASSOC);
	}

	public function execute($key = "") {
		$this->_is_executed = true;
		$statement = BaseDB::getInstance()->query($this->sql());
		if( empty($statement) ) {
			return null;
		}
		$last_id = BaseDB::getInstance()->last_id($key);
		$row_count = $statement->rowCount();

		return [
			'last_id' 	=> $last_id,
			'row_count' => $row_count
		];
	}
}
?>