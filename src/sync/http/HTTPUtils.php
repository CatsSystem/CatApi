<?php
/**
 * HTTP处理相关工具包
 *
 * @package bilibili
 * @author MagicBear<magicbearmo@gmail.com>
 * @version 1.0
 */
namespace base\sync\http;

class HTTPUtils
{
	/**
	 * 使用CURL库读取指定地址信息
	 * @param string $url 要读取的URL地址
	 * @param $timeout
	 * @param string &$error 错误
	 * @return string 地址內容 失败时为FALSE
	 */
	public static function get($url, $timeout, &$error = null)
	{
		$ch	= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, FALSE);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);

		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.83 Safari/535.11');
		
		$header = array();
		$header[] = "Accept-Language: zh-CN,zh;q=0.8,en;q=0.6"; 
		$header[] = "Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3"; 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$response	= curl_exec($ch);
		if ($response === false)
		{
			$error = curl_error($ch);
		}
		@curl_close($ch);
		unset($ch);
		
		return $response;
	}

	/**
	 * 使用CURL库POST到指定地址
	 * @param string $url 要读取的URL地址
	 * @param array $data 要提交的信息
	 * @param string &$error 错误
	 * @param bool $error_on_fail
	 * @return string 地址內容 失败时为FALSE
	 */
	public static function post_data($url, $data, &$error = null, $error_on_fail = true)
	{
		$ch	= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, FALSE);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FAILONERROR, $error_on_fail);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	 
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.83 Safari/535.11');
		
		$header = array();
		$header[] = "Accept-Language: zh-CN,zh;q=0.8,en;q=0.6"; 
		$header[] = "Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3"; 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$response	= curl_exec($ch);
		if ($response === false)
		{
			$error = curl_error($ch);
		}
		@curl_close($ch);
		unset($ch);
		return $response;
	}


	/**
	 * 使用CURL库POST到指定地址
	 * @param string $url 要读取的URL地址
	 * @param array $data 要提交的信息
	 * @param string &$error 错误
	 * @param bool $error_on_fail
	 * @return string 地址內容 失败时为FALSE
	 */
	public static function post($url, $data, &$error = null, $error_on_fail = true)
	{
		$data	= http_build_query($data);

		$ch	= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, TRUE);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FAILONERROR, $error_on_fail);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	 
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.83 Safari/535.11');
		
		$header = array();
		$header[] = "Accept-Language: zh-CN,zh;q=0.8,en;q=0.6"; 
		$header[] = "Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3"; 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$response	= curl_exec($ch);
		if ($response === false)
		{
			$error = curl_error($ch);
		}
		@curl_close($ch);
		unset($ch);
		
		return $response;
	}
}
?>