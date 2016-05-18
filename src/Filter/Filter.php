<?php

namespace LdapQuery\Filter;

use LdapQuery\Exceptions\GrammarException;

class Filter
{
    /**
     * Filter logical operators
     * 
     * @var array
     */
    protected $operators = [
        '=', // Equal to
        '~=', // Approximately equal to
        '<=', // Lexicographically less than or equal to
        '>=', // Lexicographically greater than or equal to
    ];

    /**
     * Accepted wildcards keywords
     * 
     * @var array
     */
    protected $wildcards = [
        null, // nothing
        'begins', // attribute=value*
        'ends', // attribute=*value
        'like', // attribute=*value*
    ];

    /**
     * Logical operator
     * 
     * @var string
     */
    protected $operator;

    /**
     * LDAP Attribute
     * 
     * @var string
     */
    protected $attribute;

    /**
     * Value to be compared
     * 
     * @var mixed
     */
    protected $value;

    /**
     * ldap_escape value
     * 
     * @var bool
     */
    protected $escape;

    /**
     * Value wildcard (*)
     * 
     * @var string
     */
    protected $wildcard;

    /**
     * Create a new instance of the object.
     * 
     * @param  string      $attribute
     * @param  string      $value
     * @param  string      $operator
     * @param  string|null $wildcard
     * @param  bool        $escape
     *
     * @return void
     *
     * @throws GrammarException
     */
    public function __construct($attribute, $value, $operator = '=', $wildcard = null, $escape = true)
    {
        // Check if the operator is valid.
        if (!$this->isOperatorValid($operator)) {
            throw new GrammarException(
                "Invalid filter operator. Accepted operators: " . implode(', ', $this->operators)
            );
        }

        // Check if the wildcard is valid.
        if (!$this->isWildcardValid($wildcard)) {
            throw new GrammarException(
                "Invalid wildcard keyword. Accepted keywords: " . implode(', ', $this->wildcards)
            );
        }

        // Store arguments to coresponding properties.
        $this->attribute = $attribute;
        $this->operator = $operator;
        $this->value = $value;
        $this->escape = $escape;
        $this->wildcard = $wildcard;

        // Escape value.
        $this->escape();
    }

    /**
     * Dynamically cast the object to string.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->stringify();
    }

    /**
     * Convert Filter object to a string, LDAP valid query group.
     * 
     * @return string
     */
    public function stringify()
    {
        return sprintf(
            '(%s%s%s)',
            $this->attribute,
            $this->operator,
            $this->value
        );
    }

    /**
     * Convert Filter object to array.
     *  
     * @return array
     */
    public function toArray($tab = '')
    {
        return [
            $tab . '(',
            $tab . '    ' . $this->attribute . $this->operator . $this->value,
            $tab . ')'
        ];
    }

    /**
     * LDAP Escape value and apply wildcards, if set.
     * 
     * @return void
     */
    private function escape()
    {
        if ($this->escape) {
            $this->value = ldap_escape($this->value, null, LDAP_ESCAPE_FILTER);
        }
        $this->wildcardify();
    }
    
    /**
     * Apply wildcards if provided.
     * 
     * @return void
     */
    private function wildcardify()
    {
        if ($this->wildcard === null) {
            return;
        } elseif ($this->wildcard === 'begins') {
            $this->value .= '*';
        } elseif ($this->wildcard === 'ends') {
            $this->value = '*' . $this->value;
        } elseif ($this->wildcard === 'like') {
            $this->value = '*' . $this->value .'*';
        }
    }    

    /**
     * Check if the operator is valid.
     * 
     * @param  string  $operator
     * 
     * @return boolean
     */
    private function isOperatorValid($operator)
    {
        return in_array($operator, $this->operators);
    }

    /**
     * Check if the wildcard is valid.
     * 
     * @param  string  $wildcard
     * 
     * @return boolean
     */
    private function isWildcardValid($wildcard)
    {
        return in_array($wildcard, $this->wildcards);
    }
}
