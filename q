<?php

require('vendor/autoload.php');


$builder = new \LdapQuery\Builder;
$builder->where('attrBar', 'value')
    ->andWhere('attrFoo', '<>', 'value2')
    ->orWhere('attrBaz', [1, 2, 3, 4, 5, 6, 7, 8, 9])
    ->andWhere(function($builder) {
        $builder->where('bla', 'bla2')
            ->orWhere('bla3', 'bla1');
    });

$builder->prettify(); # will generate a nice output
eval(\Psy\sh());