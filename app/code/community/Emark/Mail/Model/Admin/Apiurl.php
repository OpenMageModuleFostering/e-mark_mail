<?php

class Emark_Mail_Model_Admin_Apiurl extends Mage_Core_Model_Config_Data
{
	public function save() {
		$call = Mage::Helper('mail/api')->rest_get('',array(), $this->getScopeId(), $this->getValue());
		if(!isset($call->message)) {
			Mage::throwException(Mage::Helper('mail')->__('The Api URl is not valid.'));
		} 
		return parent::save();
	}
}
