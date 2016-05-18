<?php

namespace LdapQuery\Tests\Group;

use LdapQuery\Group\Group;
use LdapQuery\Group\GroupFactory;
use LdapQuery\Tests\UnitTestCase;

class GroupFactoryTest extends UnitTestCase
{
    /**
     * @expectedException \LdapQuery\Exceptions\GrammarException
     */
    public function testInValidOperator()
    {
        GroupFactory::build('&&');
        GroupFactory::build('||');
        GroupFactory::build('!!');
        GroupFactory::build('!&');
    }

    public function testValidOperator()
    {
        GroupFactory::build('&');
        GroupFactory::build('|');
        GroupFactory::build('!');
    }

    public function testResult()
    {
        $this->assertInstanceOf(Group::class, GroupFactory::build('&'));
    }
}
