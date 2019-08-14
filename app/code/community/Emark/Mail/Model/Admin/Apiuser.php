<?php

class Emark_Mail_Model_Admin_Apiuser extends Mage_Core_Model_Config_Data
{
	public function save() {
		$val = $this->getValue();

		if(empty($val)) {
			Mage::throwException(Mage::Helper('mail')->__('Api user is mandatory.'));	
		}
		return parent::save();
	}
}
