<?php

namespace LdapQuery;

class Builder
{	

	/**
	 * Filter group
	 * 
	 * @var \LdapQuery\FilterGroup
	 */
	protected $group;

	/**
	 * Create a new instance of the Builder
	 *
	 * @return void
	 */
	public function __construct()
	{
		// Instantiate the main filter group. This will get nested as more groups
		// will be spawned.
		$this->group = new FilterGroup;
	}

	/**
	 * Create a new instance of the Builder and return it.
	 * 
	 * @return \LdapQuery\Builder
	 */
	public static function create()
	{
		return new Builder;
	}

	/**
	 * Add a where clause to the LDAP Query
	 * 
	 * @param  string|\Closure   $attribute
	 * @param  string|array|null $operator
	 * @param  string|array|null $value
	 * @param  string            $logical
	 *
	 * @return $this
	 *
	 * @throws \LdapQuery\Exceptions\GrammarException
	 */
	public function where($attribute, $operator = null, $value = null, $logical = '&')
	{
		// If the current group operator is different from the requested
		// spawn a new group and nest the current one
		if ($this->group->getOperator() !== $logical){
			$group = new FilterGroup($logical);

			if (count($group->getFilters()) === 1) {
				$group->push($this->group->getFilters()[0]);
			} else {
				$group->push($this->group);
			}
			
			$this->group = $group;
		}

		if ($attribute instanceof \Closure) {
			$builder = new Builder;
			$attribute($builder);
			$this->group->push($builder->getGroup());
			return $this;
		}

		if ($value === null) {
			$value = $operator;
			$operator = '=';
		}

		if (is_array($value)) {
			return $this->where(function($builder) use ($attribute, $operator, $value){
				foreach($value as $v) {
					$builder->where($attribute, $operator, $v, '|');
				}
			}, null, null, $logical);
		}

		$this->group->push(Filter::create($attribute, $operator, $value));
		return $this;
	}

	/**
	 * Add a or where clause to the LDAP Query
	 * 
	 * @param  string|\Closure   $attribute
	 * @param  string|array      $operator
	 * @param  string|array|null $value
	 *
	 * @return $this
	 *
	 * @throws \LdapQuery\Exceptions\GrammarException
	 */
	public function orWhere($attribute, $operator = null, $value = null)
	{
		return $this->where($attribute, $operator, $value, '|');
	}

	/**
	 * Add a or where clause to the LDAP Query
	 * 
	 * @param  string|\Closure   $attribute
	 * @param  string|array      $operator
	 * @param  string|array|null $value
	 *
	 * @return $this
	 *
	 * @throws \LdapQuery\Exceptions\GrammarException
	 */
	public function andWhere($attribute, $operator = null, $value = null)
	{
		return $this->where($attribute, $operator, $value, '&');
	}

	/**
	 * Return Builder Group
	 * 
	 * @return \LdapQuery\FilterGroup
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Print prettified version of the query
	 * 
	 * @return void
	 */
	public function prettify()
	{
		$pretified = $this->toArray();

		foreach ($pretified as $value) {
			print($value . PHP_EOL);
		}
	}

	/**
	 * Prettify the query into array
	 * 
	 * @return array
	 */
	public function toArray($print = false)
	{
		return $this->group->toArray();
	}

	/**
	 * Cast object to string
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->group->__toString();
	}

}
