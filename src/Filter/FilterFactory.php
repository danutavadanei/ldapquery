<?php

namespace LdapQuery\Filter;

use LdapQuery\Exceptions\GrammarException;

class FilterFactory
{
    /**
     * Create a new filter and return it.
     * 
     * @param  string      $attribute
     * @param  string      $value
     * @param  string      $operator
     * @param  string|null $wildcard
     * @param  bool        $escape
     * 
     * @return Filter
     *
     * @throws GrammarException
     */
    public static function build($attribute, $value, $operator = '=', $wildcard = null, $escape = true)
    {
        return new Filter($attribute, $value, $operator, $wildcard, $escape);
    }

    /**
     * Create a new filter without escaping and return it.
     * 
     * @param  string      $attribute
     * @param  string      $value
     * @param  string      $operator
     * @param  string|null $wildcard
     * 
     * @return Filter
     *
     * @throws GrammarException
     */
    public static function buildRaw($attribute, $value, $operator = '=', $wildcard = null)
    {
        return self::build($attribute, $value, $operator, $wildcard, false);
    }
}
