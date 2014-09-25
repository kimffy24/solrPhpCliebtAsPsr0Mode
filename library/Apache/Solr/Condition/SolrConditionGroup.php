<?php

namespace Apache\Solr\Condition;

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
	public function setTopLevel( $para ){
		$this->_topLevel = $para ;
	}
	private function link(array &$arr, $linker = 'OR'){
		return implode(' '.$linker.' ', $arr);
	}
}