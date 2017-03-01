<?php
/**
 * MySQL查询接口模块
 *
 * @package bilibili
 * @author MagicBear<magicbearmo@gmail.com>
 * @version 1.0
 */
namespace base\model;

class DbQuery {
	protected function get_in_str($arr)
	{
		$joinstr = "";
		foreach ($arr as $_v)
		{
			if ($joinstr) $joinstr.=",";
			// $joinstr.=intval($_v) ? $_v : "'$_v'";
			$joinstr.="'".addslashes($_v)."'";
		}
		return $joinstr ? $joinstr : 0;
	}
	/**
	 * 创建条件查询列表
	 * @param mixed $param 参数
	 *  - 类型为ARRAY 第1个参数
	 *    - BETWEEN  查询为第2个参数至第3个参数间的数据（包含第2及第3个参数）
	 *    - EQUAL    等值为第2个参数的数据
	 *    - NE       不等于第2个参数的数据
	 *    - GT       大于第2个参数的数据
	 *    - LT       小于第2个参数的数据
	 *    - GE       大于等于第2个参数的数据
	 *    - LE       小于等于第2个参数的数据
	 *    - IN       所有位于第2个参数（数组）的数据
	 *    - SQL      直接将第2个参数的数据输出为SQL语句 (如key=a第2个参数为=a+1 则结果为`a`=a+1)
	 *    - NOTIN    所有不位于第2个参数（数组）的数据
	 *    - HEX,LIKE 二进制后等于第2个参数的数据
	 *    - LIKE     相似等于第2个参数的数据（不判定大小写 可用%作为不限长度通配符 _作为单字通配符）
	 *  - 类型为字符或数字 直接查询等于此结果的数据
	 * @param string $combine_with SQL语中中相连方式
	 * @return string
	 */
	protected function buildQueryList($param, $combine_with = " AND ")
	{
		$list = "";
		if ($param == null) return $list;
		foreach ($param as $key=>$val)
		{
			// disable next line for security problem
			if ($val===null) $val = "";
			if($list) $list.=$combine_with;
			if (is_array($val))
			{
				if ($val[0]=="BETWEEN")
				{
					$list.="(`$key` BETWEEN $val[1] AND $val[2])";
				}elseif ($val[0]=="EQUAL")
				{
					$list.="`$key`=".(intval($val[1]) ? $val[1] : "'".addslashes($val[1])."'");
				}elseif ($val[0]=="NE")
				{
					$list.="`$key`!=".(intval($val[1]) ? $val[1] : "'".addslashes($val[1])."'");
				}elseif ($val[0]=="GT")
				{
					$list.="`$key`>".(intval($val[1]) ? $val[1] : "'".addslashes($val[1])."'");
				}elseif ($val[0]=="LT")
				{
					$list.="`$key`<".(intval($val[1]) ? $val[1] : "'".addslashes($val[1])."'");
				}elseif ($val[0]=="GE")
				{
					$list.="`$key`>=".(intval($val[1]) ? $val[1] : "'".addslashes($val[1])."'");
				}elseif ($val[0]=="LE")
				{
					$list.="`$key`<=".(intval($val[1]) ? $val[1] : "'".addslashes($val[1])."'");
				}elseif ($val[0]=="IN")
				{
					$list.="`$key` IN (".$this->get_in_str($val[1]).")";
				}elseif ($val[0]=="SQL")
				{
					$list.="`$key`".$val[1];
				}elseif ($val[0]=="NOTIN")
				{
					$list.="NOT `$key` IN (".$this->get_in_str($val[1]).")";
				}elseif ($val[0]=="HEX,LIKE")
				{
					$list.="HEX(`$key`) LIKE '".addslashes($val[1])."'";
				}elseif ($val[0]=="LIKE")
				{
					$list.="`$key` LIKE '".addslashes($val[1])."'";
				}else
				{	
					echo "Query List Error on $key : $val[0]!\n";
					print_r($param);
					echo "\n";
					exit;
				}
			}else if(is_int($key)) {
				$list .= addslashes($val);
			} else
			{
				$list.="`$key`='".addslashes($val)."'";
			}
		}
		return $list;
	}
}
?>