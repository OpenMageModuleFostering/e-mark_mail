<?php
/**
 * Emark
 */
class Emark_Mbbwebservice_Model_Mail extends Mage_Core_Model_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('mbbwebservice/mail');
    }

    public function _beforeSave(){
    	if(!$this->getDate()){
    		$this->setDate(now());
    	}
    	return parent::_beforeSave();
    }
}