<?php
class Emark_Mail_Block_Admin_System_Config_Form_Field_Customerlabels extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption("-- Magento fields --", Mage::helper('mail')->__('-- Magento fields --'));
		
	    $col = Mage::getModel('customer/customer');

	    $doNotShow = array('email', 'rp_token', 'rp_token_created_at', 'increment_id', 'entity_type_id', 'entity_id', 'attribute_set_id');

	    foreach($col->getAttributes() as $label) {
	    	if(!in_array($label->getName(), $doNotShow)) {
	    	    $this->addOption($label->getName(), $label->getName());
		}
	    }

        }
        return parent::_toHtml();
    }
}
