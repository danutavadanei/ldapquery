<?php

require('vendor/autoload.php');


$builder = new \LdapQuery\Builder;
$query = \LdapQuery\Builder::create()->where('attrBar', 'value')
    ->andWhere('!attrFoo', 'value2')
    ->orWhere('attrBaz', [1, 2, 3, 4, 5, 6, 7, 8, 9])
    ->andWhere(function($builder) {
        $builder->where('bla', 'bla2')
            ->orWhere('bla3', 'bla1');
    })
    ->__toString()
;
print $builder->__tostring(); # will generate a nice output
eval(\Psy\sh());