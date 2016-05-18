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
// whereRaw - where attribute unescaped value
$builder->whereRaw('foo', 'bar*');
print $builder; // (foo=bar*)

// orWhereRaw - or where attribute unescaped value
$builder->where('foo', 'bar')
    ->orWhereRaw('foo', 'baz*'); 
print $builder; // (|(foo=bar)(foo=baz*))
```
```php
// whereNot - where attribute not value
$builder->whereNot('foo', 'bar');
print $builder; // (!(foo=bar))

// whereNotRaw - where attribute not unescaped value
$builder->whereNotRaw('foo', 'bar*');
print $builder; // (!(foo=bar*))

// orWhereNotRaw - or where attribute not unescaped value
$builder->where('foo', 'bar')
  ->orWhereNotRaw('foo', 'baz*');
print $builder;  // (|(foo=bar)(!(foo=baz*)))
```

```php
// whereBegins - where attribute begins with value
$buidler->whereBegins('foo', 'b'); 
print $builder; // (foo=b*)

// whereBeginsRaw - where attribute begins with unescaped value
$builder->whereBeginsRaw('foo', 'b)');
print $builder; // (foo=b)*)

// whereBeginsNot - where attribute not begins with value
$builder->whereNotBegins('foo', 'b');
print $builder; // (!(foo=b*))

// whereBeginsNotRaw - where attribute not begins with unescaped value
$builder->whereNotBeginsRaw('foo', 'b()');
print $builder; // (!(foo=b()*))

// orWhereBegins - or where attribute begins with value
$builder->where('foo', 'bar')
    ->orWhereBegins('foo', 'b');
print $builder; // (|(foo=bar)(foo=b*))

// orWhereBeginsRaw - or where attribute begins with unescaped value
$builder->where('foo', 'bar')
    ->orWhereBeginsRaw('foo', 'b()');
print $builder; // (|(foo=bar)(foo=b()*))

// orWhereBeginsNot - or where attribute not begins with value
$builder->where('foo', 'bar')
    ->orWhereBeginsNot('foo', 'b');
print $builder; // (|(foo=bar)(!(foo=b*)))

// orWhereBeginsNotRaw - or where attribute not begins with unescaped value
$builder->where('foo', 'bar')
    ->orWhereBeginsNotRaw('foo', 'b()');
print $builder; // (|(foo=bar)(!(foo=b()*)))
```

```php
// whereEnds - where attribute ends with value
$buidler->whereEnds('foo', 'b'); 
print $builder; // (foo=*b)

// whereEndsRaw - where attribute ends with unescaped value
$builder->whereEndsRaw('foo', 'b)');
print $builder; // (foo=*b))

// whereEndsNot - where attribute not ends with value
$builder->whereNotEnds('foo', 'b');
print $builder; // (!(foo=*b))

// whereEndsNotRaw - where attribute not ends with unescaped value
$builder->whereNotEndsRaw('foo', 'b()');
print $builder; // (!(foo=*b()))

// orWhereEnds - or where attribute ends with value
$builder->where('foo', 'bar')
    ->orWhereEnds('foo', 'b');
print $builder; // (|(foo=bar)(foo=*b))

// orWhereEndsRaw - or where attribute ends with unescaped value
$builder->where('foo', 'bar')
    ->orWhereEndsRaw('foo', 'b()');
print $builder; // (|(foo=bar)(foo=*b()))

// orWhereEndsNot - or where attribute not ends with value
$builder->where('foo', 'bar')
    ->orWhereEndsNot('foo', 'b');
print $builder; // (|(foo=bar)(!(foo=*b)))

// orWhereEndsNotRaw - or where attribute not ends with unescaped value
$builder->where('foo', 'bar')
    ->orWhereEndsNotRaw('foo', 'b()');
print $builder; // (|(foo=bar)(!(foo=*b())))
```

```php
// whereLike - where attribute like value
$buidler->whereLike('foo', 'b'); 
print $builder; // (foo=*b*)

// whereLikeRaw - where attribute like unescaped value
$builder->whereLikeRaw('foo', 'b)');
print $builder; // (foo=*b)*)

// whereLikeNot - where attribute not like value
$builder->whereNotLike('foo', 'b');
print $builder; // (!(foo=*b*))

// whereLikeNotRaw - where attribute not like unescaped value
$builder->whereNotLikeRaw('foo', 'b()');
print $builder; // (!(foo=*b()*))

// orWhereLike - or where attribute like value
$builder->where('foo', 'bar')
    ->orWhereLike('foo', 'b');
print $builder; // (|(foo=bar)(foo=*b*))

// orWhereLikeRaw - or where attribute like unescaped value
$builder->where('foo', 'bar')
    ->orWhereLikeRaw('foo', 'b()');
print $builder; // (|(foo=bar)(foo=*b()*))

// orWhereLikeNot - or where attribute not like value
$builder->where('foo', 'bar')
    ->orWhereLikeNot('foo', 'b');
print $builder; // (|(foo=bar)(!(foo=*b*)))

// orWhereLikeNotRaw - or where attribute not like unescaped value
$builder->where('foo', 'bar')
    ->orWhereLikeNotRaw('foo', 'b()');
print $builder; // (|(foo=bar)(!(foo=*b()*)))
```

### Installation

LdapQuery requires composer to install

```sh
$ composer require avadaneidanut/ldapquery
```

### Development

Want to contribute? Great! Can't wait to hear from you!

License
----

MIT
