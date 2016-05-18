# LDAP Query Builder

[![Latest Stable Version](https://poser.pugx.org/avadaneidanut/ldapquery/v/stable)](https://packagist.org/packages/avadaneidanut/ldapquery) [![Total Downloads](https://poser.pugx.org/avadaneidanut/ldapquery/downloads)](https://packagist.org/packages/avadaneidanut/ldapquery) [![Latest Unstable Version](https://poser.pugx.org/avadaneidanut/ldapquery/v/unstable)](https://packagist.org/packages/avadaneidanut/ldapquery) [![License](https://poser.pugx.org/avadaneidanut/ldapquery/license)](https://packagist.org/packages/avadaneidanut/ldapquery)

LDAP Query Builder is simple tool for easily generate queries for LDAP filtering. Quick example:

```php
$query = \LdapQuery\Builder::create()->where('attrBar', 'value')
    ->where('attrFoo', '<>' 'value2')
    ->orWhere('attrBaz', [1, 2, 3, 4, 5, 6, 7, 8, 9])
    ->where(function($builder) {
        $builder->where('bla', 'bla2')
            ->orWhere('bla3', 'bla1');
    })
    ->stringify()
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
    ->where('attrFoo', '<>' 'value2')
    ->orWhere('attrBaz', [1, 2, 3, 4, 5, 6, 7, 8, 9])
    ->where(function($builder) {
        $builder->where('bla', 'bla2')
            ->orWhere('bla3', 'bla1');
    });

$builder->toArray(); # will generate a nice output
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
use LdapQuery\Builder;
use Symfony\Component\Ldap\LdapClient;

$client = new LdapClient('ldap.example.com');
$client->bind('uid=AB1234,ou=people,o=world', 'secretpassword');

$builder = new Builder;

$details = $client->find(
    'ou=people,o=world',
    (string)$builder->where('uid', 'AB123*')->where('cn', '~=','*Danut*'),
    ['uid','cn','mail','office','mobile']
);
```

### Available methods on Builder class

```php
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
public function where($attribute, $operator = null, $value = null, $wildcard = null, $escape = true, $negation = false, $logical = '&');
    
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
public function orWhere($attribute, $operator = null, $value = null, $wildcard = null, $escape = true, $negation = false);

/**
 * Convert Group object to a string, LDAP valid query group.
 * 
 * @return string
 */
public function stringify();

/**
 * Convert Builder object to array.
 * 
 * @return array
 */
public function toArray();
```

### Dynamic where clauses
LdapQuery\Builder class has plenty dynamic "where clauses" that are transformed automatically calls to "where" or "orWhere" methods with arguments translatted from method name. This can be used as any other method example:
```php
$builder->orWhereBegins('attribute', 'value'); will be translated in
$builder->orWhere('attribute', 'value', null, 'begins', true);
```
Available Dynamic where clauses
```php
// whereRaw - unescaped where
$builder->whereRaw('foo', 'bar*');
print $builder; # (foo=bar*)

// orWhereRaw - unescaped or where
$builder->where('foo', 'bar')
    ->orWhereRaw('foo', 'baz*'); 
print $builder; // (|(foo=bar)(foo=baz*))
```
```php
// whereNot - negation
$builder->whereNot('foo', 'bar');
print $builder; // (!(foo=bar))

// whereNotRaw - unescaped negation
$builder->whereNotRaw('foo', 'bar*');
print $builder; // (!(foo=bar*))
```

And many other (I will add description asap):
```
orWhereNotRaw 
whereBegins
whereBeginsRaw
whereBeginsNot
whereBeginsNotRaw
orWhereBegins
orWhereBeginsRaw
orWhereBeginsNot
orWhereBeginsNotRaw
whereEnds
whereEndsRaw
whereEndsNot
whereEndsNotRaw
orWhereEnds
orWhereEndsRaw
orWhereEndsNot
orWhereEndsNotRaw
whereLike
whereLikeRaw
whereLikeNot
whereLikeNotRaw
orWhereLike
orWhereLikeRaw
orWhereLikeNot
orWhereLikeNotRaw
```

### Installation

LdapQuery requires composer to install

```sh
$ composer require avadaneidanut/ldapquery dev-master
```

### Development

Want to contribute? Great! Can't wait to hear from you!

License
----

MIT