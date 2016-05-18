<?php

namespace LdapQuery\Group;

use LdapQuery\Filter\Filter;
use InvalidArgumentException;
use LdapQuery\Exceptions\GrammarException;

class Group
{
    /**
     * LDAP Group logical operators
     * 
     * @var array
     */
    protected $operators = [
        '!', // Not logical operator
        '&', // And logical operator
        '|', // Or logical operator
    ];

    /**
     * Logical operator
     * 
     * @var string
     */
    protected $operator;

    /**
     * Content may have Filters and Groups.
     * 
     * @var array
     */
    protected $content = [];

    /**
     * Create a new instance of Group.
     * 
     * @param string $operator
     *
     * @throws GrammarException
     */
    public function __construct($operator = '&')
    {
        // Check if the operator is valid.
        if (!$this->isOperatorValid($operator)) {
            throw new GrammarException(
                "Invalid group operator. Accepted operators: " . implode(', ', $this->operators)
            );
        }

        $this->operator = $operator;
    }

    /**
     * Return logical operator
     * 
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Check if the group is empty.
     * 
     * @return boolean
     */
    public function isEmpty()
    {
        return count($this->content) === 0;
    }    

    /**
     * Nest this Group to provided parent.
     * 
     * @param  Group  $parent
     * 
     * @return void
     */
    public function nest(Group $parent)
    {
        $parent->push($this);
    }

    /**
     * Push a new entry in the content stack.
     * 
     * @param Filter|Group $entry
     * @param bool $clone
     * 
     * @return void
     *
     * @throws InvalidArgumentException
     * @throws GrammarException
     */
    public function push($entry, $clone = true)
    {
        if (!$entry instanceof Filter && !$entry instanceof Group) {
            throw new InvalidArgumentException("push function only accepts Filter or Group. Input was: " . get_class($entry));
        } elseif ($this->operator === '!' && count($this->content)) {
            throw new GrammarException("A 'not' group can contain only one Filter or one Group");
        }

        // By cloning the entry we avoid creating an infinite loop if somehow
        // somebody pushs $this object
        $this->content[] = $clone ? clone $entry : $entry;
    }

    /**
     * Convert Group object to a string, LDAP valid query group.
     * 
     * @return string
     */
    public function stringify()
    {
        // Early exit if no content.
        if (!count($this->content)) {
            return '';
        }
        $query = $this->beginning();
        
        array_map(function($entry) use (&$query){
            $query .= $entry;
        }, $this->content);

        return $query . $this->end();
    }

    /**
     * Convert Group object to array.
     * 
     * @return array
     */
    public function toArray($tab = '')
    {
        // Early exit if no content.
        if (!count($this->content)) {
            return [];
        }

        $query = [];
        $endTab = $tab;

        if ( ($beginning = $this->beginning()) !== ''){
            $query = [$tab . $beginning];
            // Increment tab
            $tab .= '    ';
        }
        
        array_map(function($entry) use (&$query, $tab){
            $query = array_merge($query, $entry->toArray($tab));
        }, $this->content);

        if ( ($end = $this->end()) !== ''){
            $query[] = $endTab . $this->end();
        }

        return $query;
    }

    
    /**
     * Generate query beginning.
     * 
     * @return string
     */
    private function beginning()
    {
        if (count($this->content) > 1) {
            return '(' . $this->operator;
        } elseif (count($this->content) === 1 && $this->operator === '!') {
            return '(!';
        }
        return '';
    }

    /**
     * Generate query end.
     * 
     * @return string
     */
    private function end()
    {
        if ((count($this->content) > 1) || (count($this->content) === 1 && $this->operator === '!')) {
            return ')';
        }
        return '';
    }

    /**
     * Check if the provided operator is valid.
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
     * Dynamically cast the object to string.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->stringify();
    }
}
