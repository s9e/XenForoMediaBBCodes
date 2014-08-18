<?php

class s9e_Custom
{
	public static function foobar()
	{
		return serialize(func_get_args());
	}
}