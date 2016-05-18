<?php

namespace LdapQuery\Group;

use LdapQuery\Exceptions\GrammarException;

class GroupFactory
{
    /**
     * Create a new instance of Group object and return it
     * 
     * @param  string $operator
     * 
     * @return Group
     *
     * @throws GrammarException
     */
    public static function build($operator = '&')
    {
        return new Group($operator);
    }
}
