<?php

namespace LdapQuery;

use LdapQuery\Exceptions\GrammarException;

class Filter
{
	/**
	 * Filter logical operators
	 *
	 * @var array
	 */
	const OPERATORS = [
		'<>', // Different from (not an actual LDAP operator)
		'=', // Equal to
		'~=', // Approximately equal to
		'<=', // Lexicographically less than or equal to
		'>=', // Lexicographically greater than or equal to
	];

	/**
	 * Create a new instance of the object and return it.
	 * 
	 * @param  string $attribute LDAP Attribute
	 * @param  string $operator  Logical Operator
	 * @param  string $value     Value to compare the attribute
	 * 
	 * @return \LdapQuery\Filter
	 *
	 * @throws \LdapQuery\Exceptions\GrammarException
	 */
	public static function create($attribute, $operator, $value)
	{
		return new Filter($attribute, $operator, $value);
	}

	/**
	 * Create a new instance of the object.
	 * 
	 * @param  string $attribute LDAP Attribute
	 * @param  string $operator  Logical Operator
	 * @param  string $value     Value to compare the attribute
	 * 
	 * @return void
	 *
	 * @throws \LdapQuery\Exceptions\GrammarException
	 */
	public function __construct($attribute, $operator, $value)
	{
		if (!in_array($operator, Filter::OPERATORS)) {
			throw new GrammarException("Invalid filter operator. Accepted operators: " . implode(', ', Filter::OPERATORS));
		}

		$this->attribute = $attribute;
		$this->operator = $operator;
		$this->value = $value;
	}

	/**
	 * Dynamically cast the object to string
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if ($this->operator !== '<>') {
			return sprintf('(%s%s%s)', $this->attribute, $this->operator, $this->value);
		} else {
			return sprintf('(!(%s=%s))', $this->attribute, $this->value);
		}
	}

	/**
	 * Convert the object to an array. Used to prettify the query later.
	 * 
	 * @param  string $tab
	 * 
	 * @return array
	 */
	public function toArray($tab = '')
	{
		if ($this->operator !== '<>') {
			return [$tab . sprintf('(%s%s%s)', $this->attribute, $this->operator, $this->value)];
		} else {
			return [$tab . '(!', $tab . '    ' . sprintf('(%s=%s)', $this->attribute, $this->value), $tab . ')'];
		}
	}
}
