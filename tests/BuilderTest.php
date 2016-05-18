<?php

namespace LdapQuery\Tests;

use LdapQuery\Builder;

class BuilderTest extends UnitTestCase
{
    protected function getBuilder()
    {
        return new Builder;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWildcardWhereException()
    {
        $builder = $this->getBuilder();

        $builder->whereBegins(function($query){});
    }

    public function testWhere()
    {
        $builder = $this->getBuilder();

        $builder->where('foo', 'bar')
            ->where('baz', 'foo')
            ->where('foo', 'baz')
            ->where('baz', 'foo')
            ->where('bar', '*foo*')
            ->where('baz', '(foo)');

        $this->assertEquals('(&(foo=bar)(baz=foo)(foo=baz)(baz=foo)(bar=\2afoo\2a)(baz=\28foo\29))', $builder->stringify());
    }

    public function testWhereRaw()
    {
        $builder = $this->getBuilder();

        $builder->whereRaw('bar', '*foo*')
            ->whereRaw('baz', '(foo)');

        $this->assertEquals('(&(bar=*foo*)(baz=(foo)))', $builder->stringify());
    }

    public function testOrWhere()
    {
        $builder = $this->getBuilder();

        $builder->where('foo', 'bar')
            ->orWhere('baz', 'foo')
            ->orWhere('foo', 'baz')
            ->orWhere('baz', 'foo')
            ->orWhere('bar', '*foo*')
            ->orWhere('baz', '(foo)');

        $this->assertEquals('(|(foo=bar)(baz=foo)(foo=baz)(baz=foo)(bar=\2afoo\2a)(baz=\28foo\29))', $builder->stringify());
    }

    public function testOrWhereRaw()
    {
        $builder = $this->getBuilder();

        $builder->whereRaw('bar', '*foo*')
            ->orWhereRaw('baz', '(foo)');

        $this->assertEquals('(|(bar=*foo*)(baz=(foo)))', $builder->stringify());
    }

    public function testWhereClosure()
    {
        $builder = $this->getBuilder();

        $builder->where(function($builder){
                $builder->where('foo', 'bar')
                    ->where('baz', 'foo');
            })
            ->orWhere(function($builder){
                $builder->where('baz', 'foo')
                    ->orWhere('foo', 'baz');
            });

        $this->assertEquals('(|(&(foo=bar)(baz=foo))(|(baz=foo)(foo=baz)))', $builder->stringify());
    }

    public function testWhereArray()
    {
        $builder = $this->getBuilder();

        $builder->where('foo', [1, 2, 3, 4, 5, 6, 7])
            ->where('baz', ['foo', 'bar'])
            ->orWhere('foo', 'baz');

        $this->assertEquals('(|(&(|(foo=1)(foo=2)(foo=3)(foo=4)(foo=5)(foo=6)(foo=7))(|(baz=foo)(baz=bar)))(foo=baz))', $builder->stringify());
    }

    public function testWhereNot()
    {
        $builder = $this->getBuilder();

        $builder->whereNot('foo', 'bar')
            ->orWhereNot('bar', 'foo');

        $this->assertEquals('(|(!(foo=bar))(!(bar=foo)))', $builder->stringify());

        $builder = $this->getBuilder();

        $builder->whereNot('foo', [1, 2, 3, 4, 5])
            ->where('baz', 'foo')
            ->orWhere('foobar', '**1**');

        $this->assertEquals('(|(&(!(|(foo=1)(foo=2)(foo=3)(foo=4)(foo=5)))(baz=foo))(foobar=\2a\2a1\2a\2a))', $builder->stringify());
    }

    public function testWhereNotRaw()
    {
        $builder = $this->getBuilder();

        $builder->whereNotRaw('foo', '(bar)')
            ->orWhereNotRaw('bar', '*foo*')
            ->whereNotRaw('bar', ['*1', '2*', '*2*']);

        $this->assertEquals('(&(|(!(foo=(bar)))(!(bar=*foo*)))(!(|(bar=*1)(bar=2*)(bar=*2*))))', $builder->stringify());
    }

    public function testWhereBegins()
    {
        $builder = $this->getBuilder();

        $builder->whereBegins('foo', 'bar*')
            ->orWhereBegins('foo', '**bar**');

        $this->assertEquals('(|(foo=bar\2a*)(foo=\2a\2abar\2a\2a*))', $builder->stringify());
    }
    
    public function testWhereBeginsArray()
    {
        $builder = $this->getBuilder();

        $builder->whereBegins('foo', ['bar', 'baz', 'foo']);

        $this->assertEquals('(|(foo=bar*)(foo=baz*)(foo=foo*))', $builder->stringify());
    }

    public function testWhereEnds()
    {
        $builder = $this->getBuilder();

        $builder->whereEnds('foo', 'bar*')
            ->orWhereEnds('foo', '**bar**');

        $this->assertEquals('(|(foo=*bar\2a)(foo=*\2a\2abar\2a\2a))', $builder->stringify());
    }

    public function testWhereLike()
    {
        $builder = $this->getBuilder();

        $builder->whereLike('foo', 'bar*')
            ->orWhereLike('foo', '**bar**');

        $this->assertEquals('(|(foo=*bar\2a*)(foo=*\2a\2abar\2a\2a*))', $builder->stringify());
    }
}
