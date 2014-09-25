<?php
namespace Apache\Solr\Condition;

use Apache\Solr\Exception as SolrException;

class SolrCondition implements SolrConditionInterface {
	const BOOLEAN_QUERY_AND = 1;
	const BOOLEAN_QUERY_OR = 2;
	const BOOLEAN_QUERY_NOT = 3;
	public function __construct() {
		if (! func_num_args ())
			throw new SolrException ();
		else if (func_num_args () == 1) {
			if (is_array ( func_get_arg ( 0 ) ))
				$this->construct_array ( func_get_arg ( 0 ) );
			else if (is_object ( func_get_arg ( 0 ) ))
				$this->construct_object ( func_get_arg ( 0 ), func_get_arg ( 1 ), func_get_arg ( 2 ) ? func_get_arg ( 2 ) : null );
			else $this->rowCondition( func_get_arg ( 0 ) );
		} else
			$this->addCondition ( func_get_arg ( 0 ), func_get_arg ( 1 ) ? func_get_arg ( 1 ) : null, func_get_arg ( 2 ) ? func_get_arg ( 2 ) : null, func_get_arg ( 3 ) ? func_get_arg ( 3 ) : null, func_get_arg ( 4 ) ? func_get_arg ( 4 ) : null, func_get_arg ( 5 ) ? func_get_arg ( 5 ) : null, func_get_arg ( 6 ) ? func_get_arg ( 6 ) : null );
	}
	public function toString() {
		if ($this->_conditionBuild or ! $this->_term)
			return $this->_queryString;
			
			// 1. 初始化搜索项
		$queryString = $this->_term;
		
		// 2. 搜索项合法化 如转义，引号使用等等 //3. 明确特殊符号含义
		if (substr ( $queryString, 0, 1 ) == '"' and substr ( $queryString, - 1 ) == '"') {
			// 双引号作用下考虑特殊字符
		} else {
			// 确定term是否需要引号，同时明确明确 * ? 含义
			if ((strpos ( $queryString, ' ' ) !== false)) {
				/* 这里还有一种情况 存在通配符又存在空格的情况 */
				// 暂时忽略
				$queryString = strtr ( $queryString, array (
						'"' => '\"' 
				) );
				$queryString = '"' . $queryString . '"';
			} else {
				$queryString = strtr ( $queryString, self::$_convert_fast );
				if (! $this->_wildcard)
					$queryString = strtr ( $queryString, self::$_convert_spec_fast );
			}
		}
		// 4. 模糊搜索
		if ($this->_fuzzy) {
			$queryString .= (is_numeric ( $this->_fuzzy ) && $this->_fuzzy > 0 && $this->_fuzzy < 1) ? '~' . $this->_fuzzy : '~';
		}
		// 5. 搜索权指
		if ($this->_boost && is_numeric ( $this->_boost ) && $this->_boost > 0) {
			$queryString .= '^' . $this->_boost;
		}
		// 6. 加入域
		if (preg_match ( "/^[0-9a-zA-Z-_]+$/", $this->_field ))
			$queryString = $this->_field . ':' . $queryString;
			// 7. 加入强制条件
		if ($this->_constrict && ($this->_constrict == '+' || $this->_constrict == '-'))
			$queryString = $this->_constrict . $queryString;
		
		$this->_queryString = $queryString;
		$this->_conditionBuild = true;
		return $this->_queryString;
	}
	private static $_convert = array (
			'+',
			'-',
			'&',
			'|',
			'!',
			'(',
			')',
			'[',
			']',
			'{',
			'}',
			'"',
			'^',
			'~',
			':',
			'\\' 
	);
	private static $_convertd = array (
			'\\+',
			'\\-',
			'\\&',
			'\\|',
			'\\!',
			'\\(',
			'\\)',
			'\\[',
			'\\]',
			'\\{',
			'\\}',
			'\\"',
			'\\^',
			'\\~',
			'\\:',
			'\\\\' 
	);
	private static $_convert_spec = array (
			'*',
			'?' 
	);
	private static $_convertd_spec = array (
			'\\*',
			'\\?' 
	);
	private static $_convert_fast = array (
			'+' => '\\+',
			'-' => '\\-',
			'&' => '\\&',
			'|' => '\\|',
			'!' => '\\!',
			'(' => '\\(',
			')' => '\\)',
			'[' => '\\[',
			']' => '\\]',
			'{' => '\\{',
			'}' => '\\}',
			'"' => '\\"',
			'^' => '\\^',
			'~' => '\\~',
			':' => '\\:',
			'\\' => '\\\\' 
	);
	private static $_convert_spec_fast = array (
			'*' => '\\*',
			'?' => '\\?' 
	);
	private static $_filter = array (
			'term',
			'field',
			'constrict',
			'fuzzy',
			'boost',
			'boolean',
			'wildcard' 
	);
	private $_term = null;
	private $_field = null;
	private $_constrict = null;
	private $_fuzzy = null;
	private $_boost = null;
	private $_boolean = null;
	private $_wildcard = null;
	
	private $_queryString = "*:*";
	private $_conditionBuild = false;
	private function construct_object($o) {
		return $this;
	}
	private function construct_array($a) {
		foreach ( $a as $k => $v ) {
			if (! $v)
				continue;
			if (in_array ( $k, self::$_filter )){
				$valueName = '_' . $k;
				$this->$valueName = $v;
			}
		}
	}
	private function convertSpecChar(&$string) {
		$string = str_replace ( self::$_convert, self::$_convertd, $string );
	}
	private function addCondition($term, $field = null, $wildcard = null, $boost = null, $fuzzy = null, $constrict = null, $boolean = null) {
		if ($term)
			return $this->setTerm ( $term )->setField ( $field )->setWildcard ( $wildcard )->setBoost ( $boost )->setFuzzy ( $fuzzy )->setConstrict ( $constrict )->setBoolean ( $boolean );
		else
			return $this->setTerm ( '*.*' );
	}
	private function rowCondition($query) {
		$this->_queryString = $query;
	}
	public function __call($func, $value){
		if( substr($func, 0, 3) == 'set' ){
			$item = substr($func, 3);
			$item = strtolower($item);
			if (in_array ( $item, self::$_filter ) ){
				$valueName = '_' . $item;
				$this->$valueName = $value;
				$this->_conditionBuild = false;
			}
		}
	}
	public function __invoke(){
		return $this->toString();
	}
	
	public static function add(array $conditionArray){
		return new SolrCondition($conditionArray);
	}
}