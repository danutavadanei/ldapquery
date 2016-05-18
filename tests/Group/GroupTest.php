<?php

namespace LdapQuery\Tests\Group;

use LdapQuery\Group\Group;
use LdapQuery\Tests\UnitTestCase;
use LdapQuery\Filter\FilterFactory;

class GroupTest extends UnitTestCase
{
    protected function newGroup($operator = '&')
    {
        return new Group($operator);
    }

    public function testConstruct()
    {
        $g = $this->newGroup();
        
        $this->assertEmpty($g->stringify());
        $this->assertEquals([], $g->toArray());
    }

    /**
     * @expectedException \LdapQuery\Exceptions\GrammarException
     */
    public function testInValidOperator()
    {
        $this->newGroup('&&');
    }

    /**
     * @expectedException \LdapQuery\Exceptions\GrammarException
     */
    public function testMultipleEntriesOnNotGroup()
    {
        $g = $this->newGroup('!');
        $g->push($this->newGroup());
        $g->push($this->newGroup());
    }

    public function testValidOperator()
    {
        $this->newGroup('&');
        $this->newGroup('|');
        $this->newGroup('!');
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->newGroup()->isEmpty());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPushInvalidArgument()
    {
        $this->newGroup()->push(new \StdClass);
    }

    public function testPush()
    {
        $g = $this->newGroup();
        foreach ($this->getGroups(100, '&|') as $group) {
            foreach ($this->getFilters(10) as $filter) {
                $group->push($filter);
            }
            $g->push($group);
        }
    }

    public function testNest()
    {
        $group = $this->newGroup('|');
        $groupToNest = $this->newGroup();

        $groupToNest->push(FilterFactory::build('foo', 'bar'));
        $groupToNest->push(FilterFactory::build('foo', 'bar'));
        $groupToNest->nest($group);

        $group->push(FilterFactory::build('foo', 'bar'));

        $this->assertEquals('(|(&(foo=bar)(foo=bar))(foo=bar))', $group->stringify());
    }

    public function testStringify()
    {
        $g = $this->newGroup();
        $g->push(FilterFactory::build('attribute', 'value'));

        $this->assertEquals('(attribute=value)', $g->stringify());
        $this->assertEquals((string)$g, $g->stringify());

        $g->push(FilterFactory::build('attribute', '**value**'));

        $this->assertEquals('(&(attribute=value)(attribute=\2a\2avalue\2a\2a))', $g->stringify());

        $gg = $this->newGroup('!');
        $g->push($gg);

        $this->assertEquals('(&(attribute=value)(attribute=\2a\2avalue\2a\2a))', $g->stringify());

        $gg->push(FilterFactory::build('foo', 'bar'));
        $g->push($gg);

        $this->assertEquals('(&(attribute=value)(attribute=\2a\2avalue\2a\2a)(!(foo=bar)))', $g->stringify());

        $ggg = $this->newGroup('|');
        $ggg->push($g);
        $ggg->push(FilterFactory::build('foo', 'baz', '~=', 'begins'));

        $this->assertEquals('(|(&(attribute=value)(attribute=\2a\2avalue\2a\2a)(!(foo=bar)))(foo~=baz*))', $ggg->stringify());
    }

    public function testToArray()
    {
        $g = $this->newGroup();

        $this->assertEquals([], $g->toArray());
        $this->assertEquals([], $g->toArray('    '));

        $g = $this->newGroup();
        $g->push(FilterFactory::build('foo', 'bar'));

        $this->assertEquals(
            [
                '(',
                '    foo=bar',
                ')'
            ],
            $g->toArray()
        );

        $g = $this->newGroup('!');
        $g->push(FilterFactory::build('foo', 'bar'));

        $this->assertEquals(
            [
                '(!',
                '    (',
                '        foo=bar',
                '    )',
                ')'
            ],
            $g->toArray()
        );

        $g = $this->newGroup();
        $g->push(FilterFactory::build('foo', 'bar'));
        $g->push($g);

        $this->assertEquals(
            [
                '(&',
                '    (',
                '        foo=bar',
                '    )',
                '    (',
                '        foo=bar',
                '    )',
                ')'
            ],
            $g->toArray()
        );

        $g = $this->newGroup();
        $g->push(FilterFactory::build('foo', 'bar'));
        $g->push(FilterFactory::build('foo', 'bar'));
        $g->push($g);

        $this->assertEquals(
            [
                '(&',
                '    (',
                '        foo=bar',
                '    )',
                '    (',
                '        foo=bar',
                '    )',
                '    (&',
                '        (',
                '            foo=bar',
                '        )',
                '        (',
                '            foo=bar',
                '        )',
                '    )',
                ')'
            ],
            $g->toArray()
        );
    }
}
