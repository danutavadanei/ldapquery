# LDAP Query Builder

LDAP Query Builder is simple tool for easily generate queries for LDAP filtering. Quick example:

```php
$query = (new \LdapQuery\Builder)->where('attrBar', 'value')
    ->andWhere('!attrFoo', 'value2')
    ->orWhere('attrBaz', [1, 2, 3, 4, 5, 6, 7, 8, 9])
    ->andWhere(function($builder) {
        $builder->where('bla', 'bla2')
            ->orWhere('bla3', 'bla1');
    })
    ->compile()
;
```

Output:
```
(&(|(&(attrBar=value)(!attrFoo=value2))(|(attrBaz=1)(attrBaz=2)(attrBaz=3)(attrBaz=4)(attrBaz=5)(attrBaz=6)(attrBaz=7)(attrBaz=8)(attrBaz=9)))(|(&(bla=bla2))(bla3=bla1)))
```

If you want to examine queries generated and don't manually separate groups  just:

```php
$builder = new \LdapQuery\Builder;
$query = $builder->where('attrBar', 'value')
    ->andWhere('!attrFoo', 'value2')
    ->orWhere('attrBaz', [1, 2, 3, 4, 5, 6, 7, 8, 9])
    ->andWhere(function($builder) {
        $builder->where('bla', 'bla2')
            ->orWhere('bla3', 'bla1');
    })->compile();

$builder->prettify(); # will generate a nice output
```

Output:
```
(|
        (&
            (attrBar=value)
            (!attrFoo=value2)
            (attrBaz=1)
            (attrBaz=2)
            (attrBaz=3)
            (attrBaz=4)
            (attrBaz=5)
            (attrBaz=6)
            (attrBaz=7)
            (attrBaz=8)
            (attrBaz=9)
        )
        (&
            (bla=bla2)
            (bla3=bla1)
        )
)
```


### Installation

Dillinger requires composer to install

```sh
$ composer require ldapquery/builder dev-master
```

### To-Do
* Add not where clause

### Development

Want to contribute? Great! Can't wait to hear from you!

License
----

MIT