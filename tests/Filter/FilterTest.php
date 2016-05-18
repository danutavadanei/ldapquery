<?php

namespace LdapQuery\Tests\Filter;

use LdapQuery\Filter\Filter;
use LdapQuery\Tests\UnitTestCase;

class FilterTest extends UnitTestCase
{
    protected function newFilter($attribute, $value, $operator = '=', $wildcard = null, $escape = true)
    {
        return new Filter($attribute, $value, $operator, $wildcard, $escape);
    }

    /**
     * @expectedException \LdapQuery\Exceptions\GrammarException
     */
    public function testInValidOperator()
    {
        $this->newFilter('attribute', 'value', '<>');
    }

    /**
     * @expectedException \LdapQuery\Exceptions\GrammarException
     */
    public function testInValidWildcard()
    {
        $this->newFilter('attribute', 'value', '=', 'something');
    }

    public function testEscapedValue()
    {
        $this->assertEquals('(attribute=\2avalue\2a)', $this->newFilter('attribute', '*value*')->stringify());
        $this->assertEquals('(attribute=\28value\29\28\2a\2a\29)', $this->newFilter('attribute', '(value)(**)')->stringify());
        $this->assertEquals('(attribute=/\5c\2a\28\29value\5c\2a/)', $this->newFilter('attribute', '/\*()value\\*/')->stringify());
        $this->assertEquals('(attribute=\2a\2a\2a\2a\2a)', $this->newFilter('attribute', '*****')->stringify());
    }

    public function testUnescapedValue()
    {
        $this->assertEquals('(attribute=*value*)', $this->newFilter('attribute', '*value*', '=', null, false)->stringify());
        $this->assertEquals('(attribute=(value)(**))', $this->newFilter('attribute', '(value)(**)' , '=', null, false)->stringify());
        $this->assertEquals('(attribute=/\*()value\\*/)', $this->newFilter('attribute', '/\*()value\\*/', '=', null, false)->stringify());
        $this->assertEquals('(attribute=*****)', $this->newFilter('attribute', '*****', '=', null, false)->stringify());
    }

    public function testWildcardBegins()
    {
        $this->assertEquals('(attribute=value*)', $this->newFilter('attribute', 'value', '=', 'begins')->stringify());
        $this->assertEquals('(attribute=value\2a*)', $this->newFilter('attribute', 'value*', '=', 'begins')->stringify());
    }

    public function testWildcardEnds()
    {
        $this->assertEquals('(attribute=*value)', $this->newFilter('attribute', 'value', '=', 'ends')->stringify());
        $this->assertEquals('(attribute=*\2avalue)', $this->newFilter('attribute', '*value', '=', 'ends')->stringify());
    }
    public function testWildcardLike()
    {
        $this->assertEquals('(attribute=*value*)', $this->newFilter('attribute', 'value', '=', 'like')->stringify());
        $this->assertEquals('(attribute=*\2avalue\2a*)', $this->newFilter('attribute', '*value*', '=', 'like')->stringify());
    }

    public function testToArray()
    {
        $expected = [
            "(",
            "    attribute=value",
            ")",
        ];

        $this->assertEquals($expected, $this->newFilter('attribute', 'value')->toArray());
    }

    public function testLogicalOperator()
    {
        $this->assertEquals('(attribute=value)', $this->newFilter('attribute', 'value')->stringify());
        $this->assertEquals('(attribute=value)', $this->newFilter('attribute', 'value', '=')->stringify());
        $this->assertEquals('(attribute~=value)', $this->newFilter('attribute', 'value', '~=')->stringify());
        $this->assertEquals('(attribute<=value)', $this->newFilter('attribute', 'value', '<=')->stringify());
        $this->assertEquals('(attribute>=value)', $this->newFilter('attribute', 'value', '>=')->stringify());
    }
}
