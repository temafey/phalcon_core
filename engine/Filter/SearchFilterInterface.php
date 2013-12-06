<?php
/**
 * @namespace
 */
namespace Engine\Filter;

/**
 * Filter Interface
 *
 * @category   Engine
 * @package    Filter
 */
interface SearchFilterInterface
{
	CONST COLUMN_ID = 'ID';
	CONST COLUMN_NAME = 'NAME';
	
	CONST CRITERIA_EQ = "eq";
	CONST CRITERIA_NOTEQ = "noteq";
	CONST CRITERIA_MORE = "more";
	CONST CRITERIA_LESS = "less";
	CONST CRITERIA_MORER = "morer";
	CONST CRITERIA_LESSER = "lesser";
	CONST CRITERIA_LIKE = "like";
	CONST CRITERIA_BEGINS = "begin";
    const CRITERIA_IN = "IN";
    const CRITERIA_NOTIN = "NOTIN";
	
	/**
	 * Apply filter to datasource
	 * 
	 * @param mixed $dataSource
	 */
	public function applyFilter($dataSource);
}