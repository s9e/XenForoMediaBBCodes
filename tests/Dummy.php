<?php

namespace s9e\XenForoMediaBBCodes\Tests;

class Dummy
{
	public static $loggedCalls = array();
	public function __construct()
	{
		static::$loggedCalls = array();
	}
	public function __call($methodName, $args)
	{
		static::$loggedCalls[] = array($methodName, $args);
	}
}