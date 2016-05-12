<?php

namespace LdapQuery;

class Builder
{
	/**
	 * Array containing all the clauses.
	 * 
	 * @var array
	 */
	protected $clauses = [];

	/**
	 * Current Logical operator
	 * 
	 * @var string
	 */
	protected $currentLogicalOperator = '&';
	
	/**
	 * Context of logical operator
	 * 
	 * @var string
	 */
	protected $context;
	
	/**
	 * Check if dynamic call to where function was made
	 * 
	 * @var boolean
	 */
	protected $dynamicWhere = false;
		
	/**
	 * Pretty Query
	 * 
	 * @var array
	 */
	protected $prettyQuery = [];

	/**
	 * Generate unique context id
	 * 
	 * @return void
	 */
	private function generateContext()
	{
		$this->context = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 50);
	}
	/**
	 * Add a where clause. If $attribute is a Closure, a new Builder will
	 * be passed as the first argumetn to the lambda function and will be
	 * treated as a simple (attribute=value) at the end.
	 * 
	 * @param  string|\Closure   $attribute LDAP Attribute or Closure
	 * @param  null|string|array $value     Value to be compared
	 * 
	 * @return $this
	 */
	public function where($attribute, $value = null)
	{
		if ($this->dynamicWhere === false) {
			$this->currentLogicalOperator = '&';
		}

		if ($this->context === null) {
			$this->generateContext();
		}

		if ($attribute instanceof \Closure) {
			
			// Create a new builder
			$builder = new self;

			// Call \Closure with the new builder
			$attribute($builder);

			// Store query in the clause variable
			$clause = $builder->compile();

			// Pretified clause
			$prettyQuery = $builder->prettify();	

			var_dump($prettyQuery);		
		}

		if (!isset($clause)) {

			if (is_array($value)) {
				$builder = new self;
				foreach ($value as $v) {
					$builder->orWhere($attribute, $v);
				}

				$clause = $builder->compile();

				// Pretified clause
				$prettyQuery = $builder->prettify();	
			} else {
				$clause = sprintf(
					'(%s=%s)',
					$attribute,
					$value
				);
			}
		}
		
		$this->clauses[] = [
			'clause'   => $clause,
			'context'  => $this->context,
			'operator' => $this->currentLogicalOperator,
			'pretty'   => isset($prettyQuery) ? $prettyQuery : null
		];

		$this->dynamicWhere = false;
		
		return $this;
	}

	/**
	 * Compile the LDAP Query
	 * 
	 * @return string
	 */
	public function compile()
	{	
		
		$context = $this->clauses[0]['context'];

		$query = '';

		$tab = '';

		foreach ($this->clauses as $item) {

			// Prettify the query
			if ($context !== $item['context']) {
				$tab .= '    ';
				foreach ($this->prettyQuery as $key => $value) {
					$this->prettyQuery[$key] = $tab . $value;
				}
				array_unshift($this->prettyQuery, '(' . $operator);
			}
			if ($pretty = $item['pretty']) {
				foreach ($pretty as $value) {
					$this->prettyQuery[] = $tab . $value;
				}
			} else {
				$this->prettyQuery[] = $tab . $item['clause'];
			}
			if ($context !== $item['context']) {
				$this->prettyQuery[] = ')';
			}

			// Proceed with compiling query
			if ($context !== $item['context']) {
				$this->context = $item['context'];
				$query = '(' . $operator . $query . ')' . $item['clause'];
			} else {
				$query .= $item['clause'];
			}

			$operator = $item['operator'];
		}

		if (count($this->clauses) > 1) {
			$query = '(' . $operator . $query . ')';
		}

		return $query;
	}

	public function prettify($print = false)
	{
		if ($print) {
			foreach ($this->prettyQuery as $value) {
				print($value . PHP_EOL);
			}
		}
		return $this->prettyQuery;
	}

	/**
	 * Dynamically handle function calls to the object
	 * 
	 * @param  string $name
	 * @param  array $arguments
	 * 
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{	
		$this->dynamicWhere = true;

		if ($name === 'orWhere' && $this->currentLogicalOperator !== '|') {
			$this->currentLogicalOperator = '|';
			$this->generateContext();
			return $this->where(...$arguments);
		} elseif ($name === 'andWhere' && $this->currentLogicalOperator !== '&') {
			$this->currentLogicalOperator = '&';
			$this->generateContext();
			return $this->where(...$arguments);
		} elseif ($name === 'orWhere' || $name === 'andWhere') {
			return $this->where(...$arguments);
		}

		return $this->{$name}(...$arguments);
	}
}
