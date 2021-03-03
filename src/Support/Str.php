<?php

namespace sunilib\Support;

/**
 * 字符串工具类
 */
final class Str{
	
	/**
	 * 驼峰转下划线缓存
	 *
	 * @var array
	 */
	protected static $snakeCache = [];
	
	/**
	 * 下划线转驼峰(首字母小写) 缓存
	 *
	 * @var array
	 */
	protected static $camelCache = [];
	
	/**
	 * 下划线转驼峰(首字母大写)
	 *
	 * @var array
	 */
	protected static $studlyCache = [];
	
	/**
	 * 检查字符串中是否包含某些字符串
	 *
	 * @param string       $haystack
	 * @param string|array $needles
	 * @return bool
	 */
	public static function contains($haystack, $needles){
		foreach((array)$needles as $needle){
			if($needle != '' && mb_strpos($haystack, $needle) !== false){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 检查字符串是否以某些字符串结尾
	 *
	 * @param string       $haystack
	 * @param string|array $needles
	 * @return bool
	 */
	public static function endsWith($haystack, $needles){
		foreach((array)$needles as $needle){
			if((string)$needle === static::subString($haystack, -mb_strlen($needle))){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 字符串截取，支持中文和其他编码
	 *
	 * @param string $value 验证的值
	 * @param int    $start 开始位置
	 * @param int    $length 截取长度
	 * @param string $charset 字符编码
	 * @return string
	 */
	public static function subString($value, $start = 0, $length = null, $charset = null){
		if(function_exists("mb_substr"))
			$slice = mb_substr($value, $start, $length, $charset);
		elseif(function_exists('iconv_substr')){
			$length = is_null($length) ? $length = 'iconv_strlen($str, $charset)' : $length;
			$charset = is_null($charset) ? $charset = 'ini_get("iconv.internal_encoding")' : $charset;
			$slice = iconv_substr($value, $start, $length, $charset);
			if(false === $slice){
				$slice = '';
			}
		}else{
			$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re [$charset], $value, $match);
			$slice = join("", array_slice($match [0], $start, $length));
		}
		return $slice;
	}
	
	/**
	 * 检查字符串是否以某些字符串开头
	 *
	 * @param string       $haystack
	 * @param string|array $needles
	 * @return bool
	 */
	public static function startsWith($haystack, $needles){
		foreach((array)$needles as $needle){
			if($needle != '' && mb_strpos($haystack, $needle) === 0){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 字符串转小写
	 *
	 * @param string $value
	 * @return string
	 */
	public static function lower($value){
		return mb_strtolower($value, 'UTF-8');
	}
	
	/**
	 * 字符串转大写
	 *
	 * @param string $value
	 * @return string
	 */
	public static function upper($value){
		return mb_strtoupper($value, 'UTF-8');
	}
	
	/**
	 * 获取字符串的长度
	 *
	 * @param string $value
	 * @return int
	 */
	public static function length($value){
		return mb_strlen($value);
	}
	
	/**
	 * 驼峰转下划线
	 *
	 * @param string $value
	 * @param string $delimiter
	 * @param bool   $isCache
	 * @return string
	 */
	public static function snake($value, $delimiter = '_', $isCache = true){
		$key = $value;
		
		if(isset(self::$snakeCache[$key][$delimiter])){
			return self::$snakeCache[$key][$delimiter];
		}
		
		if(!ctype_lower($value)){
			$value = preg_replace('/\s+/u', '', $value);
			
			$value = self::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
		}
		
		return $isCache ? self::$snakeCache[$key][$delimiter] = $value : $value;
	}
	
	/**
	 * 清除驼峰转下划线缓存
	 */
	public static function clearSnakeCache(){
		self::$snakeCache = [];
	}
	
	/**
	 * 下划线转驼峰(首字母小写)
	 *
	 * @param string $value
	 * @param bool   $isCache
	 * @return string
	 */
	public static function camel($value, $isCache = true){
		if(isset(self::$camelCache[$value])){
			return self::$camelCache[$value];
		}
		
		$value = lcfirst(self::studly($value));
		return $isCache ? self::$camelCache[$value] = $value : $value;
	}
	
	/**
	 * 清除下划线转驼峰(首字母小写)缓存
	 */
	public static function clearCamelCache(){
		self::$snakeCache = [];
	}
	
	/**
	 * 下划线转驼峰(首字母大写)
	 *
	 * @param string $value
	 * @param bool   $isCache
	 * @return string
	 */
	public static function studly($value, $isCache = true){
		$key = $value;
		
		if(isset(self::$studlyCache[$key])){
			return self::$studlyCache[$key];
		}
		
		$value = ucwords(str_replace(['-', '_'], ' ', $value));
		$value = str_replace(' ', '', $value);
		
		return $isCache ? self::$studlyCache[$key] = $value : $value;
	}
	
	/**
	 * 清除下划线转驼峰(首字母大写)缓存
	 */
	public static function clearStudlyCache(){
		self::$snakeCache = [];
	}
	
	/**
	 * 转为首字母大写的标题格式
	 *
	 * @param string $value
	 * @return string
	 */
	public static function title($value){
		return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
	}
	
	/**
	 * 实现多种字符编码方式
	 *
	 * @param string $input 数据源
	 * @param string $_output_charset 输出的字符编码
	 * @param string $_input_charset 输入的字符编码
	 * @return string
	 */
	public static function charsetEncode($input, $_output_charset, $_input_charset){
		if(!isset ($_output_charset))
			$_output_charset = $_input_charset;
		if($_input_charset == $_output_charset || $input == null){
			$output = $input;
		}elseif(function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($input, $_output_charset, $_input_charset);
		}elseif(function_exists("iconv")){
			$output = iconv($_input_charset, $_output_charset, $input);
		}else{
			throw new \RuntimeException("不支持 $_input_charset 到 $_output_charset 编码！");
		}
		return $output;
	}
	
	/**
	 * 实现多种字符解码方式
	 *
	 * @param string $input 数据源
	 * @param string $_input_charset 输入的字符编码
	 * @param string $_output_charset 输出的字符编码
	 * @return string
	 */
	public static function charsetDecode($input, $_input_charset, $_output_charset){
		if($_input_charset == $_output_charset || $input == null){
			$output = $input;
		}elseif(function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($input, $_output_charset, $_input_charset);
		}elseif(function_exists("iconv")){
			$output = iconv($_input_charset, $_output_charset, $input);
		}else{
			throw new \RuntimeException("不支持 $_input_charset 到 $_output_charset 解码！");
		}
		return $output;
	}
	
	/**
	 * 获取随机字符串
	 *
	 * @param int $length
	 * @param int $type
	 * @return string
	 */
	public static function random($length = 16, $type = 5){
		$pool = [
			0 => '0123456789',
			1 => 'abcdefghijklmnopqrstuvwxyz',
			2 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
		];
		
		$poolStr = '';
		if(0 == $type) $poolStr = $pool[0];
		if(1 == $type) $poolStr = $pool[1];
		if(2 == $type) $poolStr = $pool[2];
		if(3 == $type) $poolStr = $pool[0].$pool[1];
		if(4 == $type) $poolStr = $pool[1].$pool[2];
		if(5 == $type) $poolStr = $pool[0].$pool[1].$pool[2];
		
		return self::substr(str_shuffle(str_repeat($poolStr, $length)), 0, $length);
	}
	
	/**
	 * 截取字符串
	 *
	 * @param string   $string
	 * @param int      $start
	 * @param int|null $length
	 * @return string
	 */
	public static function substr($string, $start, $length = null){
		return mb_substr($string, $start, $length, 'UTF-8');
	}
	
	/**
	 * 生成随机字符串
	 *
	 * @param string $factor
	 * @return string
	 */
	public static function nonceHash32($factor = ''){
		return md5(uniqid(md5(microtime(true).$factor), true));
	}
	
	/**
	 * 创建订单编号
	 *
	 * @param string $prefix
	 * @return string
	 */
	public static function makeOrderSn($prefix = ''){ // 取出订单编号
		$datetime = date('YmdHis');
		$microtime = explode(' ', microtime());
		$microtime = intval($microtime[0] ? $microtime[0] * 100000 : 100000);
		
		$nonceStr = substr(uniqid(), 7, 13);
		$nonceStr = str_split($nonceStr, 1);
		$nonceStr = array_map('ord', $nonceStr);
		$nonceStr = substr(implode(null, $nonceStr), -8);
		
		return $prefix.$datetime.$microtime.$nonceStr;
	}
	
	/**
	 * 解析Url Query
	 *
	 * @param string $url url地址或URL query参数
	 * @return array
	 */
	public static function parseUrlQuery($url){
		$index = strpos($url, "?");
		$url = $index === false ? $url : substr($url, $index);
		parse_str($url, $result);
		return $result;
		//
		//		//分隔数据
		//		$querys = explode("&", $url);
		//		unset($url);
		//		$regx = "/\\[(.*?)\\]/";
		//		//返回的结果
		//		$result = [];
		//		foreach($querys as $key => $val){
		//			list($name, $value) = explode('=', $val, 2);
		//			unset($querys[$key]);
		//
		//			$name = trim($name);
		//			//是否为数组
		//			if(preg_match($regx, $name, $matches)){
		//				$matches = $matches[1];
		//				$name = substr($name, 0, strpos($name, "[") - 1);
		//				if(empty($matches))
		//					$result[$name][] = $value;
		//				else
		//					$result[$name][$matches] = $value;
		//			}else{
		//				$result[$name] = $value;
		//			}
		//		}
		//		return $result;
	}
	
	/**
	 * 把数组所有元素按照“参数=参数值”的模式用“&”字符拼接成字符串
	 *
	 * @param array  $params 关联数组
	 * @param string $handleFunc 值处理函数
	 * @return string
	 */
	public static function buildUrlQuery($params, $handleFunc = null){
		if(!is_callable($handleFunc)) $handleFunc = function($key, $val){
			$type = gettype($val);
			if($type == 'object' || $type == 'array') return '';
			
			$val = urlencode($val);
			return $key.'='.$val;
		};
		
		$result = '';
		$i = 0;
		foreach($params as $key => $val){
			$str = $handleFunc($key, $val);
			if($str === '') continue;
			$result .= ($i === 0 ? '' : '&').$str;
			$i++;
		}
		return $result;
	}
	
	/**
	 * 安全处理-字符串或数组转数组
	 *
	 * @param mixed         $value
	 * @param string        $format
	 * @param string        $delimiter
	 * @param bool|\Closure $filter
	 * @return array
	 */
	public static function explode($value, $format = 'intval', $delimiter = ',', $filter = true){
		if(!is_array($value)){
			$value = is_string($value) ? explode($delimiter, $value) : [$value];
		}
		
		//		foreach($value as $k => &$v){
		//			if('intval' == $format){
		//				$v = intval($v);
		//			}elseif('floatval' == $format){
		//				$v = floatval($v);
		//			}elseif('boolval' == $format){
		//				$v = boolval($v);
		//			}elseif('long2ip' == $format){
		//				$v = long2ip($v);
		//			}
		//		}
		//		unset($v);
		
		$value = array_map($format, $value);
		
		if($filter !== false){
			if($filter === true){
				$value = array_filter($value);
			}else{
				$value = array_filter($value, $filter);
			}
		}
		
		return array_values($value);
	}
	
	/**
	 * 安全处理-数组转字符串
	 *
	 * @param mixed  $value
	 * @param string $format
	 * @param string $delimiter
	 * @return string
	 */
	public static function implode($value, $format = 'intval', $delimiter = ','){
		//先转换为数组，进行安全过滤
		$value = self::explode($value, $format, $delimiter);
		
		//去除重复
		$value = array_unique($value);
		
		//再次转换为字符串
		return implode(",", $value);
	}
	
	/**
	 * Determine if a given string matches a given pattern.
	 *
	 * @param string|array $pattern
	 * @param string       $value
	 * @return bool
	 */
	public static function is($pattern, $value){
		$patterns = Arr::wrap($pattern);
		
		if(empty($patterns)){
			return false;
		}
		
		foreach($patterns as $pattern){
			// If the given value is an exact match we can of course return true right
			// from the beginning. Otherwise, we will translate asterisks and do an
			// actual pattern match against the two strings to see if they match.
			if($pattern == $value){
				return true;
			}
			
			$pattern = preg_quote($pattern, '#');
			
			// Asterisks are translated into zero-or-more regular expression wildcards
			// to make it convenient to check if the strings starts with the given
			// pattern such as "library/*", making any string check convenient.
			$pattern = str_replace('\*', '.*', $pattern);
			
			if(preg_match('#^'.$pattern.'\z#u', $value) === 1){
				return true;
			}
		}
		
		return false;
	}
}
