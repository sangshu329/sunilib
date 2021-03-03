<?php
namespace sunilib\Support;

/**
 * 数组工具类
 */
final class Arr{
	
	/**
	 * 是否为关联数组
	 *
	 * @param array $arr 数组
	 * @return bool
	 */
	public static function isAssoc($arr){
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	
	/**
	 * 不区分大小写的in_array实现
	 *
	 * @param $value
	 * @param $array
	 * @return bool
	 */
	public static function in($value, $array){
		return in_array(strtolower($value), array_map('strtolower', $array));
	}
	
	/**
	 * 对数组排序
	 *
	 * @param array $param 排序前的数组
	 * @return array
	 */
	public static function sort(&$param){
		ksort($param);
		reset($param);
		return $param;
	}
	
	/**
	 * 除去数组中的空值和和附加键名
	 *
	 * @param array $params 要去除的数组
	 * @param array $filter 要额外过滤的数据
	 * @return array
	 */
	public static function filter(&$params, $filter = ["sign", "sign_type"]){
		foreach($params as $key => $val){
			if($val == "" || (is_array($val) && count($val) == 0)){
				unset ($params [$key]);
			}else{
				$len = count($filter);
				for($i = 0; $i < $len; $i++){
					if($key == $filter [$i]){
						unset ($params [$key]);
						array_splice($filter, $i, 1);
						break;
					}
				}
			}
		}
		return $params;
	}
	
	/**
	 * 数组栏目获取
	 *
	 * @param array  $array
	 * @param string $column
	 * @param string $index_key
	 * @return array
	 */
	public static function column(array $array, $column, $index_key = null){
		$result = [];
		foreach($array as $row){
			$key = $value = null;
			$keySet = $valueSet = false;
			if($index_key !== null && array_key_exists($index_key, $row)){
				$keySet = true;
				$key = (string)$row[$index_key];
			}
			if($column === null){
				$valueSet = true;
				$value = $row;
			}elseif(is_array($row) && array_key_exists($column, $row)){
				$valueSet = true;
				$value = $row[$column];
			}
			if($valueSet){
				if($keySet){
					$result[$key] = $value;
				}else{
					$result[] = $value;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * 解包数组
	 *
	 * @param array        $array
	 * @param string|array $keys
	 * @return array
	 */
	public static function uncombine(array $array, $keys = null){
		$result = [];
		
		if($keys){
			$keys = is_array($keys) ? $keys : explode(',', $keys);
		}else{
			$keys = array_keys(current($array));
		}
		
		foreach($keys as $index => $key){
			$result[$index] = [];
		}
		
		foreach($array as $item){
			foreach($keys as $index => $key){
				$result[$index][] = isset($item[$key]) ? $item[$key] : null;
			}
		}
		
		return $result;
	}
	
	/**
	 * 数组去重-二维数组
	 *
	 * @param array  $array
	 * @param string $key
	 * @return array
	 */
	public static function multiUnique($array, $key){
		$i = 0;
		$temp_array = [];
		$key_array = [];
		
		foreach($array as $val){
			if(!in_array($val[$key], $key_array)){
				$key_array[$i] = $val[$key];
				$temp_array[$i] = $val;
			}
			$i++;
		}
		return $temp_array;
	}
	
	/**
	 * 无极限分类
	 *
	 * @param array         $list 数据源
	 * @param callable|null $itemHandler 额外处理回调函数
	 * @param int           $pid 父id
	 * @param array         $options
	 * @return array
	 */
	public static function tree(array $list, callable $itemHandler = null, $pid = 0, array $options = []){
		$options = array_merge([
			'id'           => 'id', // 要检索的ID键名
			'parent'       => 'pid', // 要检索的parent键名
			'child'        => 'child', // 要存放的子结果集
			'with_unknown' => false, // 是否把未知的上级当成1级返回
		], $options);
		
		if(is_null($itemHandler)){
			$itemHandler = function($level, &$value){ };
		}
		
		$level = 0;
		$handler = function(array &$list, $pid) use (&$handler, &$level, &$itemHandler, &$options){
			$level++;
			$idKey = $options['id'];
			$parentKey = $options['parent'];
			$childKey = $options['child'];
			
			$result = [];
			foreach($list as $key => $value){
				if($value[$parentKey] == $pid){
					unset ($list[$key]);
					
					$itemHandler($level, $value);
					
					$childList = $handler($list, $value[$idKey]);
					if(!empty($childList)){
						$value[$childKey] = $childList;
					}
					
					$result[] = $value;
					reset($list);
				}
			}
			$level--;
			
			return $result;
		};
		
		$result = $handler($list, $pid);
		
		// 是否把未知的上级当成1级返回
		if(!empty($list) && $options['with_unknown']){
			$level = 1;
			foreach($list as &$value){
				$itemHandler($level, $value);
			}
			unset($value);
			
			$result = array_merge($result, array_values($list));
		}
		
		return $result;
	}
	
	/**
	 * 树转tree
	 *
	 * @param array  $list
	 * @param string $child
	 * @return array
	 */
	public static function treeToList($list, $child = 'child'){
		$handler = function($list, $child) use (&$handler){
			$result = [];
			foreach($list as $key => &$val){
				$result[] = &$val;
				unset($list[$key]);
				if(isset($val[$child])){
					$result = array_merge($result, $handler($val[$child], $child));
					unset($val[$child]);
				}
			}
			unset($val);
			return $result;
		};
		return $handler($list, $child);
	}
	
	/**
	 * 转换数组里面的key
	 *
	 * @param array $arr
	 * @param array $keyMaps
	 * @return array
	 */
	public static function transformKeys(array $arr, array $keyMaps){
		foreach($keyMaps as $oldKey => $newKey){
			if(!array_key_exists($oldKey, $arr)) continue;
			
			if(is_callable($newKey)){
				[$newKey, $value] = call_user_func($newKey, $arr[$oldKey], $oldKey, $arr);
				$arr[$newKey] = $value;
			}else{
				$arr[$newKey] = $arr[$oldKey];
			}
			unset($arr[$oldKey]);
		}
		return $arr;
	}
	
	/**
	 * 将多维数组展平为单个级别
	 *
	 * @param array $array
	 * @param int   $depth
	 * @return array
	 */
	public static function flatten($array, $depth = INF){
		$result = [];
		
		foreach($array as $item){
			if(!is_array($item)){
				$result[] = $item;
			}else{
				$values = $depth === 1 ? array_values($item) : self::flatten($item, $depth - 1);
				
				foreach($values as $value){
					$result[] = $value;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * 确定给定的键是否存在于提供的数组中
	 *
	 * @param \ArrayAccess|array $array
	 * @param string|int         $key
	 * @return bool
	 */
	public static function exists($array, $key){
		if($array instanceof \ArrayAccess){
			return $array->offsetExists($key);
		}
		
		return array_key_exists($key, $array);
	}
	
	/**
	 * 支持使用“点”表示法从数组中获取项
	 *
	 * @param \ArrayAccess|array $array
	 * @param string|int         $key
	 * @param mixed              $default
	 * @return mixed
	 */
	public static function get($array, $key, $default = null){
		if(!self::accessible($array)){
			return value($default);
		}
		
		if(is_null($key)){
			return $array;
		}
		
		if(self::exists($array, $key)){
			return $array[$key];
		}
		
		if(strpos($key, '.') === false){
			return isset($array[$key]) ? $array[$key] : value($default);
		}
		
		foreach(explode('.', $key) as $segment){
			if(self::accessible($array) && self::exists($array, $segment)){
				$array = $array[$segment];
			}else{
				return value($default);
			}
		}
		
		return $array;
	}
	
	/**
	 * 支持使用“点”表示法检查数组中是否存在一个或多个项
	 *
	 * @param \ArrayAccess|array $array
	 * @param string|array       $keys
	 * @return bool
	 */
	public static function has($array, $keys){
		$keys = (array)$keys;
		
		if(!$array || $keys === []){
			return false;
		}
		
		foreach($keys as $key){
			$subKeyArray = $array;
			
			if(self::exists($array, $key)){
				continue;
			}
			
			foreach(explode('.', $key) as $segment){
				if(self::accessible($subKeyArray) && self::exists($subKeyArray, $segment)){
					$subKeyArray = $subKeyArray[$segment];
				}else{
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * 支持使用“点”表示法将数组项设置为给定值
	 * 如果没有给方法指定键，整个数组将被替换
	 *
	 * @param array  $array
	 * @param string $key
	 * @param mixed  $value
	 * @return array
	 */
	public static function set(&$array, $key, $value){
		if(is_null($key)){
			return $array = $value;
		}
		
		$keys = explode('.', $key);
		
		while(count($keys) > 1){
			$key = array_shift($keys);
			
			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if(!isset($array[$key]) || !is_array($array[$key])){
				$array[$key] = [];
			}
			
			$array = &$array[$key];
		}
		
		$array[array_shift($keys)] = $value;
		
		return $array;
	}
	
	/**
	 * 从数组里面获取指定的数据
	 *
	 * @param array $data
	 * @param array $keys
	 * @return array
	 */
	public static function only($data, array $keys){
		$result = [];
		foreach($keys as $key){
			if(isset($data[$key])){
				$result[$key] = $data[$key];
			}
		}
		
		return $result;
	}
	
	/**
	 * 从数组中获取一个或指定数量的随机值
	 *
	 * @param array    $array
	 * @param int|null $number
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public static function random($array, $number = null){
		$requested = is_null($number) ? 1 : $number;
		
		$count = count($array);
		
		if($requested > $count){
			throw new \InvalidArgumentException(
				"You requested {$requested} items, but there are only {$count} items available."
			);
		}
		
		if(is_null($number)){
			return $array[array_rand($array)];
		}
		
		if((int)$number === 0){
			return [];
		}
		
		$keys = array_rand($array, $number);
		
		$results = [];
		
		foreach((array)$keys as $key){
			$results[] = $array[$key];
		}
		
		return $results;
	}
	
	/**
	 * 打乱给定数组并返回结果
	 *
	 * @param array    $array
	 * @param int|null $seed
	 * @return array
	 */
	public static function shuffle($array, $seed = null){
		if(is_null($seed)){
			shuffle($array);
		}else{
			mt_srand($seed);
			shuffle($array);
			mt_srand();
		}
		
		return $array;
	}
	
	/**
	 * 如果给定的值不是数组且不是null，将其包装在一个数组中
	 *
	 * @param mixed $value
	 * @return array
	 */
	public static function wrap($value){
		if(is_null($value)){
			return [];
		}
		
		return is_array($value) ? $value : [$value];
	}
	
	/**
	 * 将数组使用点展平多维关联数组
	 *
	 * @param array  $array
	 * @param string $prepend
	 * @return array
	 */
	public static function dot($array, $prepend = ''){
		$results = [];
		
		foreach($array as $key => $value){
			if(is_array($value) && !empty($value)){
				$results = array_merge($results, self::dot($value, $prepend.$key.'.'));
			}else{
				$results[$prepend.$key] = $value;
			}
		}
		
		return $results;
	}
	
	/**
	 * 给定值是否可由数组访问
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public static function accessible($value){
		return is_array($value) || $value instanceof \ArrayAccess;
	}
	
	/**
	 * 返回数组中通过给定真值测试的第一个元素
	 *
	 * @param array|iterable $array
	 * @param callable|null  $callback
	 * @param mixed          $default
	 * @return mixed
	 */
	public static function first($array, callable $callback = null, $default = null){
		if(is_null($callback)){
			if(empty($array)){
				return value($default);
			}
			
			foreach($array as $item){
				return $item;
			}
		}
		
		foreach($array as $key => $value){
			if(call_user_func($callback, $value, $key)){
				return $value;
			}
		}
		
		return value($default);
	}
	
	/**
	 * 返回数组中通过给定真值测试的最后一个元素
	 *
	 * @param array         $array
	 * @param callable|null $callback
	 * @param mixed         $default
	 * @return mixed
	 */
	public static function last($array, callable $callback = null, $default = null){
		if(is_null($callback)){
			return empty($array) ? value($default) : end($array);
		}
		
		return self::first(array_reverse($array, true), $callback, $default);
	}
	
	/**
	 * 检测数组所有元素是否都符合指定条件
	 *
	 * @param array|iterable  $array
	 * @param string|callable $callback
	 * @return bool
	 */
	public static function every($array, $callback){
		foreach($array as $k => $v){
			if(!$callback($v, $k)){
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 解析字符串为数组
	 *
	 * @param string $string
	 * @return array
	 */
	public static function parse($string){
		$array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
		
		if(strpos($string, ':')){
			$value = [];
			foreach($array as $val){
				$val = explode(':', $val);
				if(isset($val[1]) && $val[0] !== ''){
					$value[$val[0]] = $val[1];
				}else{
					$value[] = $val[0];
				}
			}
		}else{
			$value = $array;
		}
		
		return $value;
	}
	
	/**
	 * 数组解析为字符串
	 *
	 * @param array $array
	 * @return string
	 */
	public static function stringOf($array){
		$result = '';
		
		if(self::isAssoc($array)){
			foreach($array as $key => $val){
				$result .= "{$key}:$val\n";
			}
		}else{
			$result = implode("\n", $array);
		}
		
		return $result;
	}
}
