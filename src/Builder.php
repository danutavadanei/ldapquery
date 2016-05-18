<?php

namespace LdapQuery;


use BadMethodCallException;
use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LdapQuery\Exceptions\GrammarException;
use LdapQuery\Filter\FilterFactory;
use LdapQuery\Group\Group;
use LdapQuery\Group\GroupFactory;

class Builder
{   
    /**
     * Filter group
     * 
     * @var Group
     */
    protected $group;

    /**
     * Accepted dynamicWhere methods names
     * 
     * @var array
     */
    protected $dynamicWheres = [
        'whereRaw',
        'orWhereRaw',
        'whereNot',
        'whereNotRaw',
        'orWhereNot',
        'orWhereNotRaw',
        'whereBegins',
        'whereBeginsRaw',
        'whereBeginsNot',
        'whereBeginsNotRaw',
        'orWhereBegins',
        'orWhereBeginsRaw',
        'orWhereBeginsNot',
        'orWhereBeginsNotRaw',
        'whereEnds',
        'whereEndsRaw',
        'whereEndsNot',
        'whereEndsNotRaw',
        'orWhereEnds',
        'orWhereEndsRaw',
        'orWhereEndsNot',
        'orWhereEndsNotRaw',
        'whereLike',
        'whereLikeRaw',
        'whereLikeNot',
        'whereLikeNotRaw',
        'orWhereLike',
        'orWhereLikeRaw',
        'orWhereLikeNot',
        'orWhereLikeNotRaw',
    ];

    /**
     * Create a new instance of the Builder
     *
     * @return void
     */
    public function __construct()
    {
        // Create or store the main filter group. This will get nested as more groups
        // will be spawned.
        $this->group = GroupFactory::build();
    }

    /**
     * Convert Group object to a string, LDAP valid query group.
     * 
     * @return string
     */
    public function stringify()
    {
        return $this->group->stringify();
    }

    /**
     * Convert Builder object to array.
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->group->toArray();
    }

    /**
     * Add a where clause to the LDAP Query. Defaulted to & logical, acts like a andWhere.
     * 
     * @param  string|Closure    $attribute
     * @param  string|array|null $operator
     * @param  string|array|null $value
     * @param  string|null       $wildcard
     * @param  bool              $escape
     * @param  string            $logical
     *
     * @return $this
     *
     * @throws GrammarException
     */
    public function where($attribute, $operator = null, $value = null, $wildcard = null, $escape = true, $negation = false, $logical = '&')
    {
        // If value is null, we assume that value is allocated to $operator
        // and the we want default operator (=)
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        // If value is an array, use a closure to generate a or group with all the
        // values.
        if (is_array($value)) {
            return $this->where(function($builder) use ($attribute, $value, $wildcard, $escape){
                foreach ($value as $v) {
                    $builder->orWhere($attribute, '=', $v, $wildcard, $escape);
                }
            }, null, null, null, null, $negation, $logical);
        }

        // Solve context accordingly to the rules.
        $group = $this->solveContext($logical, $negation);

        // If the attribute is a closure then we assume that we want to create a new context
        // and push it into the current group
        if ($attribute instanceof Closure) {
            $closureBuilder = new self();
            $attribute($closureBuilder);
            $group->push($closureBuilder->group);
            return $this;
        }

        $group->push(FilterFactory::build($attribute, $value, $operator, $wildcard, $escape));
        return $this;
    }

    /**
     * Add a or where clause to the LDAP Query.
     * 
     * @param  string|Closure   $attribute
     * @param  string|array|null $operator
     * @param  string|array|null $value
     * @param  string|null       $wildcard
     * @param  bool              $escape
     *
     * @return $this
     *
     * @throws GrammarException
     */
    public function orWhere($attribute, $operator = null, $value = null, $wildcard = null, $escape = true, $negation = false)
    {
        return $this->where($attribute, $operator, $value, $wildcard, $escape, $negation, '|');
    }

    /**
     * Handle dynamic where clauses to the LDAP Query. 
     * 
     * @param  string $method
     * @param  string|Closure $attribute
     * @param  string|array|null $operator
     * @param  string|array|null $value
     * 
     * @return $this
     *
     * @throws GrammarException
     * @throws InvalidArgumentException
     */
    protected function dynamicWhere($method, $attribute, $operator = null, $value = null)
    {
        $options = explode('_', snake_case($method));

        if (Str::startsWith($method, 'where')) {
            $method = 'where';
        } else {
            $method = 'orWhere';
        }

        // Add escape option.
        $escape = !in_array('raw', $options);

        // Add wildcard option.
        if (in_array('begins', $options)) {
            $wildcard = 'begins';
        } elseif (in_array('ends', $options)) {
            $wildcard = 'ends';
        } elseif (in_array('like', $options)) {
            $wildcard = 'like';
        } else {
            $wildcard = null;
        }

        if ($wildcard !== null && $attribute instanceof Closure) {
            throw new InvalidArgumentException("Dynamic where clauses don't support wildcard operator if attribute is a Closure");
        }

        $negation = in_array('not', $options);

        return $this->{$method}($attribute, $operator, $value, $wildcard, $escape, $negation);
    }

    /**
     * Solve the group context. 
     * 
     * @param string $logical
     * @param bool   $negation
     * 
     * @return Builder
     */
    protected function solveContext($logical, $negation)
    {
        if ($this->group->getOperator() !== $logical){
            // If the current group operator is different from the requested
            // spawn a new group and nest the current one
            $group = GroupFactory::build($logical);
            $this->group->nest($group);
            $this->group = $group;
        }

        if ($negation) {
            // Create a new ! group, push it without clone and return to
            // build query on it.
            $context = GroupFactory::build('!');
            $group = GroupFactory::build();
            $context->push($group, false);
            $this->group->push($context, false);
            return $group;
        }
        return $this->group;
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
     * Handle dynamic method calls into the method.
     *
     * @param string  $method
     * @param array   $parameters
     * 
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->dynamicWheres)) {
            return $this->dynamicWhere($method, ...$parameters);
        }

        $className = static::class;

        throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }
}
