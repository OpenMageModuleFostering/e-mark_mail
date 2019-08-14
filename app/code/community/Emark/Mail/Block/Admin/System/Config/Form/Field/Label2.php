<?php

class Emark_Mail_Block_Admin_System_Config_Form_Field_Label extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected $_groupRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                'cataloginventory/adminhtml_form_field_customergroup', '',
                array('is_render_to_js_template' => true)
            );
            $this->_groupRenderer->setClass('customer_group_select');
            $this->_groupRenderer->setExtraParams('style="width:120px"');
        }

        return $this->_groupRenderer;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
	/*echo "<pre>";
	$col = Mage::getModel('customer/customer');
	foreach($col->getAttributes() as $c) {
		var_dump($c->getName());
	}*/
        $this->addColumn('customer_group_id', array(
            'label' => Mage::helper('customer')->__('Magento Fields'),
            'renderer' => $this->_getGroupRenderer(),
        ));
        $this->addColumn('min_sale_qty', array(
            'label' => Mage::helper('cataloginventory')->__('E-mark Mail Fields'),
	    'renderer' => $this->_getGroupRenderer(),
            'style' => 'width:100px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('cataloginventory')->__('Add Minimum Qty');
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('customer_group_id')),
            'selected="selected"'
        );
    }

}
