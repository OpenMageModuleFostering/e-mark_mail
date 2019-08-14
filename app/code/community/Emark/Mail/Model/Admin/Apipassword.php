<?php

class Emark_Mail_Model_Admin_Apipassword extends Mage_Core_Model_Config_Data
{
	public function save() {

		$dat = $this->getData();

		if(!Mage::Helper('mail/api')->checkConfigSuccessLogin($dat['groups']['emark_settings']['fields']['emark_user']['value'], $this->getValue(), $this->getScopeId())) {
			Mage::throwException(Mage::Helper('mail')->__("Can't login into Api, please check user and password."));		
		} else {
	
		}
		return parent::save();
	}
}
