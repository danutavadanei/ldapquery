<?php

namespace LdapQuery;

use LdapQuery\Exceptions\GrammarException;
use LdapQuery\Exceptions\NoFiltersException;

class FilterGroup
{
	/**
	 * Logical operators within filters inside the group.
	 *
	 * @var array
	 */
	const OPERATORS = [
		'|', // OR
		'&', // AND
		'!', // NOT
	];

	/**
	 * Filter group operator
	 * 
	 * @var string
	 */
	protected $operator;

	/**
	 * Array containing \LdapQuery\Filter and \LdapQuery\FilterGroup objects
	 * 
	 * @var array
	 */
	protected $filters = [];

	/**
	 * Create a new instance of FilterGroup and return it.
	 * 
	 * @param string $operator
	 *
	 * @return \LdapQuery\FilterGroup
	 *
	 * @throws \LdapQuery\Exceptions\GrammarException
	 */
	public static function create($operator = '&')
	{
		return new FilterGroup($operator);
	}

	/**
	 * Create a new instance of FilterGroup
	 * 
	 * @param string $operator
	 *
	 * @return void
	 *
	 * @throws \LdapQuery\Exceptions\GrammarException
	 */
	public function __construct($operator = '&')
	{
		if (!in_array($operator, FilterGroup::OPERATORS)) {
			throw new GrammarException("Invalid filter group operator. Accepted operators: " . implode(', ', FilterGroup::OPERATORS));
		}

		$this->operator = $operator;
	}

	/**
	 * Add filter to the group
	 * 
	 * @param  Filter|FilterGroup $filter
	 * 
	 * @return void
	 */
	public function push($filter)
	{
		if ($filter instanceof FilterGroup) {
			if (count($filter->getFilters()) === 1) {
				return $this->push($filter->getFilters()[0]);
			}
		}
		$this->filters[] = $filter;
	}

	/**
	 * Return filter group operator
	 * 
	 * @return string
	 */
	public function getOperator()
	{
		return $this->operator;
	}

	/**
	 * Return group filters
	 * 
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * Cast the filter group to string
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if (!count($this->filters)) {
			return '';
		}

		$group = '';
		
		foreach ($this->filters as  $filter) {
			$group .= $filter;
		}

		return '(' . $this->operator . $group . ')';
	}

	/**
	 * Cast the filter group to array.
	 * Used to prettify the query.
	 * 
	 * @return array
	 */
	public function toArray($tab = '')
	{
		if (!count($this->filters)) {
			return [];
		}

		$return = [$tab . '(' . $this->operator];

		$filterTab = $tab . '   ';

		foreach ($this->filters as $filter) {
			$return = array_merge($return, $filter->toArray($filterTab));
		}

		return array_merge($return, [$tab . ')']);
	}
}
