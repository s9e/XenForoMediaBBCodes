<?php

class XenForo_Application
{
	public static $options = array();
	public static function get()
	{
		return (object) self::$options;
	}
}