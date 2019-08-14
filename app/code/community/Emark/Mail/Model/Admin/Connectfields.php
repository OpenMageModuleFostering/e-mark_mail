<?php

class Emark_Mail_Model_Admin_Connectfields extends Mage_Core_Model_Config_Data {

	 /**
     * Process data after load
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();

        $value = Mage::helper('mail/connectfields')->makeArrayFieldValue($value);
        $this->setValue($value);
    }

    /**
     * Prepare data before save
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $value = Mage::helper('mail/connectfields')->makeStorableArrayFieldValue($value);
        $this->setValue($value);
    }

	
	public function save() {
                return parent::save();
        }
}
