<?php

class XenForo_Model_BbCode
{
	public static $loggedCalls = array();
	public function __construct()
	{
		self::$loggedCalls = array();
	}
	public function __call($methodName, $args)
	{
		self::$loggedCalls[] = array($methodName, $args);
	}
}