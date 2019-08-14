<?php

/**
 * @desc rewrite a class for rendering the dropdown configuration fields
 */

class Emark_Mail_Block_Admin_System_Config_Form_Field_Label extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected $_subscriberRenderer;
    protected $_customerRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected function _getSubscriberRenderer()
    {
        if (!$this->_subscriberRenderer) {
            $this->_subscriberRenderer = $this->getLayout()->createBlock(
                'mail/admin_system_config_form_field_subscriberlabels', '',
                array('is_render_to_js_template' => true)
            );
	    $this->_subscriberRenderer->setName('groups[emark_labels][fields][emark_label][value][#{_id}][subscriber_label]');
            $this->_subscriberRenderer->setClass('customer_group_select');
            $this->_subscriberRenderer->setExtraParams('style="width:150px"');
        }

        return $this->_subscriberRenderer;
    }


    protected function _getCustomerRenderer()
    {
        if (!$this->_customerRenderer) {
            $this->_customerRenderer = $this->getLayout()->createBlock(
                'mail/admin_system_config_form_field_customerlabels', '',
                array('is_render_to_js_template' => true)
            );
	    $this->_customerRenderer->setName('groups[emark_labels][fields][emark_label][value][#{_id}][customer_label]');
            $this->_customerRenderer->setClass('customer_group_select');
            $this->_customerRenderer->setExtraParams('style="width:120px"');
        }

	return $this->_customerRenderer;
    }


    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('customer_label', array(
            'label' => Mage::helper('mail')->__('Magento Fields'),
	    'renderer' => $this->_getCustomerRenderer()
        ));
        $this->addColumn('subscriber_label', array(
            'label' => Mage::helper('mail')->__('E-mark Mail Fields'),
	    'renderer' => $this->_getSubscriberRenderer()
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('mail')->__('Add E-mark connect field');
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {

        $row->setData(
            'option_extra_attr_' . $this->_getSubscriberRenderer()->calcOptionHash($row->getData('subscriber_label')),
            'selected="selected"'
        );

	$row->setData(
            'option_extra_attr_' . $this->_getCustomerRenderer()->calcOptionHash($row->getData('customer_label')),
            'selected="selected"'
        );
    }

}
