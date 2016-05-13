<?php

namespace LdapQuery;

class BuilderFacade
{
	/**
	 * Dynamically handle static calls and forward to a new instance of a builder
	 * 
	 * @param  string $name
	 * @param  array $arguments
	 * 
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments)
	{
		return (new Builder)->{$name}(...$arguments);
	}
}