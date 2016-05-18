<?php

namespace LdapQuery\Tests;

use LdapQuery\Group\GroupFactory;
use LdapQuery\Filter\FilterFactory;

class UnitTestCase extends \PHPUnit_Framework_TestCase
{
    protected function getFilters($count, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $filters = [];
        for ($i = 0; $i < $count; $i++) {
            $filters[] = FilterFactory::build(
                substr(str_shuffle($chars), 0, 10),  
                substr(str_shuffle($chars), 0, 10)   
            );
        }
        return $filters;
    }

    protected function getGroups($count, $chars = '&|!')
    {
        $groups = [];
        for ($i = 0; $i < $count; $i++) {
            $groups[] = GroupFactory::build(
                substr(str_shuffle($chars), 0, 1),  
                substr(str_shuffle($chars), 0, 1)  
            );
        }
        return $groups;
    }
}