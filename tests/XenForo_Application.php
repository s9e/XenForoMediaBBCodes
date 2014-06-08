<?php

class XenForo_Application
{
	public static $options = [];
	public static function get()
	{
		return (object) self::$options;
	}
}