<?php
/**
 * 组合对象Condition
 * 
 * @author Jiefzz Lon
 *
 */

namespace Apache\Solr\Condition;

/**
 * SolrCondition 聚合类，提供Solr多条件的链接方法，生成子查询命令等等
 * @author Jiefzz Lon
 *
 */
class SolrConditionGroup implements SolrConditionInterface {
	private $_topLevel = true;
	private $_conditionListAnd = array ();
	private $_conditionListOr = array ();
	private $_conditionListNot = array ();
	private static $_conditionRelationship = array (
			SolrCondition::BOOLEAN_QUERY_AND,
			SolrCondition::BOOLEAN_QUERY_OR,
			SolrCondition::BOOLEAN_QUERY_NOT 
	);
	private static $_conditionConnector = array (
			SolrCondition::BOOLEAN_QUERY_AND => 'AND',
			SolrCondition::BOOLEAN_QUERY_OR => 'OR',
			SolrCondition::BOOLEAN_QUERY_NOT => 'NOT' 
	);
	
	/**
	 * 连接一个SolrCondition|SolrConditionGroup类，并指定链接方式<br />
	 * 返回对象本身
	 * @param SolrConditionInterface $cond
	 * @param enum SolrCondition::BOOLEAN_QUERY_NOT|SolrCondition::BOOLEAN_QUERY_OR|SolrCondition::BOOLEAN_QUERY_AND|0
	 * @return \Apache\Solr\Condition\SolrConditionGroup
	 */
	public function add(SolrConditionInterface $cond, $relationship = 0) {
		if(($cond instanceof SolrConditionGroup) or method_exists($cond, 'setTopLevel'))
			$cond->setTopLevel(false);
		switch($relationship){
			case SolrCondition::BOOLEAN_QUERY_NOT:
				$this->_conditionListNot[] = $cond->toString();
				break;
			case SolrCondition::BOOLEAN_QUERY_OR:
				$this->_conditionListOr[] = $cond->toString();
				break;
			default:
			case SolrCondition::BOOLEAN_QUERY_AND:
				$this->_conditionListAnd[] = $cond->toString();
				break;
		}
		return $this;
	}
	/**
	 * 返回Solr查询串
	 * @return String SolrConditionString
	 * @see \Apache\Solr\Condition\SolrConditionInterface::toString()
	 */
	public function toString() {
		$finnalCondition = array();
		if(count($this->_conditionListAnd))
			$finnalCondition[SolrCondition::BOOLEAN_QUERY_AND] = $this->link($this->_conditionListAnd, self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_AND]);
		if(count($this->_conditionListOr)) 
			$finnalCondition[SolrCondition::BOOLEAN_QUERY_OR] = $this->link($this->_conditionListOr, self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_OR]);
		if(count($this->_conditionListNot))
			$finnalCondition[SolrCondition::BOOLEAN_QUERY_NOT] = $this->link($this->_conditionListOr, self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_NOT]);
			/*$this->link($this->_conditionListAnd, self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_AND])
			.self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_OR]
			.$this->link($this->_conditionListOr, self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_OR])
			.self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_NOT]
			.$this->link($this->_conditionListOr, self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_NOT]);*/
		$lastResult = (isset($finnalCondition[SolrCondition::BOOLEAN_QUERY_AND]))?
				$finnalCondition[SolrCondition::BOOLEAN_QUERY_AND]:'';
		$lastResult = (isset($finnalCondition[SolrCondition::BOOLEAN_QUERY_OR]))?((!$lastResult)?$lastResult:$lastResult
				.' '.self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_OR].' ').$finnalCondition[SolrCondition::BOOLEAN_QUERY_OR]
			:$lastResult;
		$lastResult = (isset($finnalCondition[SolrCondition::BOOLEAN_QUERY_NOT]))?((!$lastResult)?$lastResult:$lastResult
				.' '.self::$_conditionConnector[SolrCondition::BOOLEAN_QUERY_NOT].' ').$finnalCondition[SolrCondition::BOOLEAN_QUERY_NOT]
			:$lastResult;
		if(!$this->_topLevel)
			$lastResult = '('.$lastResult.')';
		$this->_conditionListAnd = array();
		$this->_conditionListNot = array();
		$this->_conditionListOr = array();
		$this->_topLevel = true;
		return $lastResult;
	}
	/**
	 * 声明构造子查询域
	 * @param boolean $para
	 */
	public function setTopLevel( $para ){
		$this->_topLevel = $para ;
	}
	/**
	 * (non-PHPdoc)
	 * @see \Apache\Solr\Condition\SolrConditionInterface::__invoke()
	 */
	public function __invoke(){
		return $this->toString();
	}
	private function link(array &$arr, $linker = 'OR'){
		return implode(' '.$linker.' ', $arr);
	}
}