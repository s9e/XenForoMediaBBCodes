<?php

class XenForo_Model_TemplateModification extends s9e\XenForoMediaBBCodes\Tests\Dummy
{
	static public $modification = false;
	public function getModificationByKey()
	{
		return self::$modification;
	}
}