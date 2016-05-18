<?php

namespace LdapQuery\Tests\Filter;

use LdapQuery\Tests\UnitTestCase;
use LdapQuery\Filter\Filter;
use LdapQuery\Filter\FilterFactory;

class FilterFactoryTest extends UnitTestCase
{
    public function testBuild()
    {
        $this->assertEquals(new Filter('attribute', 'value'), FilterFactory::build('attribute', 'value'));
    }

    public function testBuildRaw()
    {
        $this->assertEquals(new Filter('attribute', 'value', '=', null, false), FilterFactory::buildRaw('attribute', 'value'));
    }

    public function testOutput()
    {
        $this->assertEquals((new Filter('attribute', 'value'))->stringify(), FilterFactory::build('attribute', 'value')->stringify());
        $this->assertEquals((new Filter('attribute', '*value*'))->stringify(), FilterFactory::build('attribute', '*value*')->stringify());
    }

    public function testOutputRaw()
    {
        $this->assertEquals((new Filter('attribute', 'value', '=', null, false))->stringify(), FilterFactory::buildRaw('attribute', 'value')->stringify());
        $this->assertEquals((new Filter('attribute', '*value*', '=', null, false))->stringify(), FilterFactory::buildRaw('attribute', '*value*')->stringify());
    }

    /**
     * @expectedException \LdapQuery\Exceptions\GrammarException
     */
    public function testInValidOperator()
    {
        $f = FilterFactory::build('attribute', 'value', '<>');
    }

    /**
     * @expectedException \LdapQuery\Exceptions\GrammarException
     */
    public function testInValidOperatorRaw()
    {
        $f = FilterFactory::buildRaw('attribute', 'value', '<>');
    }

    /**
     * @expectedException \LdapQuery\Exceptions\GrammarException
     */
    public function testInValidWildcard()
    {
        $f = FilterFactory::build('attribute', 'value', '=', 'something');
    }

    /**
     * @expectedException \LdapQuery\Exceptions\GrammarException
     */
    public function testInValidWildcardRaw()
    {
        $f = FilterFactory::buildRaw('attribute', 'value', '=', 'something');
    }
}