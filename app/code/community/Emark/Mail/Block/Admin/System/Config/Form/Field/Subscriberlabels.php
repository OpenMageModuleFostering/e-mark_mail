<?php
class Emark_Mail_Block_Admin_System_Config_Form_Field_Subscriberlabels extends Mage_Core_Block_Html_Select
{

    private $notice = false;
    public function _toHtml()
    {
	$shopName = $this->getRequest()->getParam('store', '');
	if(empty($shopName)) {
		$website = $this->getRequest()->getParam('website', '');
		$groupId = Mage::getModel('core/website')->getCollection()->addFieldToFilter('code',$website)->getFirstItem()->getDefaultGroupId();
		$shopId = Mage::getModel('core/store_group')->getCollection()->addFieldToFilter('group_id',$groupId)->getFirstItem()->getDefaultStoreId();
		
	} else {
		$shopId = Mage::getModel('core/store')->getCollection()->addFieldToFilter('code',$shopName)->getFirstItem()->getStoreId();
	}

	if(!Mage::Helper('mail/api')->_init($shopId) && !$this->notice) {
		Mage::getSingleton('core/session')->addNotice('Can\'t connect to the rest api, check in the General settings');
		$this->notice = true;
	}

        if (!$this->getOptions()) {
            $this->addOption("-- E-mark Mail fields --", Mage::helper('mail')->__('-- E-mark Mail fields --'));

	    $labels = Mage::Helper('mail/api')->getSubscriberLabel($shopId);

	    if($labels->valid) {
		foreach($labels->data as $label) {
			$this->addOption($label->name, $label->name);
		}
	    }
        }
        return parent::_toHtml();
    }
}
