# LDAP Query Builder

LDAP Query Builder is simple tool for easily generate queries for LDAP filtering. Quick example:

```php
$query = \LdapQuery\Builder::create()->where('attrBar', 'value')
    ->andWhere('attrFoo', '<>' 'value2')
    ->orWhere('attrBaz', [1, 2, 3, 4, 5, 6, 7, 8, 9])
    ->andWhere(function($builder) {
        $builder->where('bla', 'bla2')
            ->orWhere('bla3', 'bla1');
    })
    ->__toString()
;
```

Output:
```
(&(|(&(attrBar=value)(!(attrFoo=value2)))(|(attrBaz=1)(attrBaz=2)(attrBaz=3)(attrBaz=4)(attrBaz=5)(attrBaz=6)(attrBaz=7)(attrBaz=8)(attrBaz=9)))(|(bla=bla2)(bla3=bla1)))
```

If you want to examine queries generated and don't manually separate groups  just:

```php
$builder = new \LdapQuery\Builder;
$builder->where('attrBar', 'value')
    ->andWhere('attrFoo', '<>' 'value2')
    ->orWhere('attrBaz', [1, 2, 3, 4, 5, 6, 7, 8, 9])
    ->andWhere(function($builder) {
        $builder->where('bla', 'bla2')
            ->orWhere('bla3', 'bla1');
    });

$builder->prettify(); # will generate a nice output
```

Output:
```
(&
   (|
      (&
         (attrBar=value)
         (!
             (attrFoo=value2)
         )
      )
      (|
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
   )
   (|
      (bla=bla2)
      (bla3=bla1)
   )
)
```

Usage with sympfony ldap component:
```php
use LdapQuery\BuilderFacade;
use Symfony\Component\Ldap\LdapClient;

$client = new LdapClient('ldap.example.com');
$client->bind('uid=AB1234,ou=people,o=world', 'secretpassword');

$details = $client->find(
    'ou=people,o=world',
    (string)BuilderFacade::where('uid', 'AB123*')->andWhere('cn', '~=','*Danut*'),
    ['uid','cn','mail','office','mobile']
);
```

### Note

Please keep in mind that all attributes or values will not be LDAP escaped. You have to do that manually.

### Installation

LdapQuery requires composer to install

```sh
$ composer require avadaneidanut/querybuilder dev-master
```

### Development

Want to contribute? Great! Can't wait to hear from you!

License
----

MIT
