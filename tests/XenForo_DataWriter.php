<?php

class XenForo_DataWriter
{
	public static function create($className)
	{
		return new $className;
	}
}