<?php
/**
 * 初次实践接口编程
 * @author Jiefzz Lon
 *
 */
namespace Apache\Solr\Condition;

/**
 * SolrConditionInterface 接口类，仅仅规定solrCondition必须有的方法，期望返回及格的Solr查询串
 * @author Jiefzz Lon
 *
 */
interface SolrConditionInterface {
	/**
	 * 生成Solr查询字符串
	 * @return String SolrConditionString
	 */
	public function toString();
	/**
	 * 期望以调用函数方式调用类时执行 $this->toString() 方法
	 */
	public function __invoke();
}