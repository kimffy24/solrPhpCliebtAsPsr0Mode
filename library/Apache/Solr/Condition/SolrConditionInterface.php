<?php
namespace Apache\Solr\Condition;

interface SolrConditionInterface {
	public function toString();
	public function __invoke();
}